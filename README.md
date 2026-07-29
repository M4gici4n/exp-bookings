# Experience Bookings API

A small HTTP API for an experiences platform. Providers publish experiences, each experience offers several sessions (a date, a maximum capacity and a price), and users book seats for a session and can later cancel the booking. The project is built with PHP 8.5 and Symfony, using Doctrine ORM over MariaDB, and follows Domain-Driven Design with a hexagonal architecture.

## Running the project

The whole environment runs on Docker and is driven through a `Makefile`, so no local PHP or database installation is required. You only need Docker and `make`.

Running `make` on its own prints every available command with a short description, which is the easiest way to discover what is available. The three commands you need to get started are:

- `make run` starts all the services (PHP-FPM, Nginx and MariaDB).
- `make create-db` creates the database and runs the migrations.
- `make test` runs the test suite.

Once the containers are up and the database has been created, the API is available at `http://localhost:8080`. A phpMyAdmin instance is also running at `http://localhost:8081`, where you can browse the database and inspect its tables and data.

> **Note**
> MariaDB is configured without a persistent volume, so stopping and recreating the container drops the schema. If that happens, running `make create-db` again restores it.

## Architecture

The code is organised around business capabilities rather than technical layers. There are two functional modules, `Experiences` and `Bookings`, plus a `Shared` module that holds everything common to both (the base building blocks for aggregates and domain events, value objects such as `Ulid` and `Money`, the command and query buses, and the HTTP plumbing).

Each module is split into three layers following the hexagonal approach. The `Domain` layer contains the aggregates, value objects, domain events and the repository interfaces (the ports). It has no knowledge of Symfony, Doctrine or HTTP. The `Application` layer contains the use cases, expressed as commands and queries with their handlers. The `Infrastructure` layer contains the adapters: the Doctrine repositories, the message buses, the migrations and the HTTP controllers. Dependencies always point inwards, from infrastructure towards the domain, and the domain depends on nothing outside itself.

The write side and the read side are kept separate following CQRS. Writes are expressed as commands (`RegisterExperience`, `CreateSession`, `BookSeats`, `CancelBooking`) dispatched through a command bus whose handlers mutate the aggregates and persist them. Each command runs inside a database transaction. Reads are expressed as queries dispatched through a separate query bus, and their handlers return read-model DTOs (the `*View` classes) built from the aggregates, so the domain entities are never exposed directly over HTTP. Keeping the two buses apart means read operations are not wrapped in a write transaction and the two sides can evolve independently.

Identifiers are generated at the HTTP edge, before the command is dispatched, so that the caller receives the id in the response and the same id is used consistently through the whole operation.

### Identifiers

Entities are identified with ULIDs rather than UUIDs. A ULID is shorter to store and read, and because its most significant bits are a timestamp it is monotonic: identifiers generated later sort after earlier ones. That property is friendly to database indexes, since new rows are appended at the end of the primary-key index instead of being inserted at random positions, which avoids page splits and keeps the index compact. It also means the records can be ordered by id and the newest one is simply the largest, which the listing endpoints rely on. ULIDs are stored as a fixed `CHAR(26)`.

### Money and amounts

Prices and totals are wrapped in a `Money` value object rather than kept as plain numbers. The brief did not ask for more than one currency, but modelling money explicitly leaves the door open to supporting different currencies later without touching the callers. Amounts are held as integers in the currency's minor units (for example cents) instead of floats, to avoid the rounding errors that floating-point arithmetic introduces in monetary calculations.

### Concurrency when booking seats

Popular sessions can sell out in minutes with many people booking at the same time, so the booking path is designed to be safe under concurrency. Seats are never reserved by reading the available count into PHP, checking it and writing it back, which would be open to race conditions between concurrent requests. Instead the reservation is a single atomic statement that decrements the available seats only when enough are still available (`UPDATE ... SET available_seats = available_seats - :seats WHERE id = :id AND available_seats >= :seats`). If the statement affects no rows, the session either does not exist or does not have enough seats left, and the operation is rejected. This makes overselling impossible regardless of how many requests arrive at once, because the check and the update happen indivisibly in the database.

### Notifications

The brief only requires the approach to sending mail, not a real delivery, so notifications are faked: when a booking is confirmed or cancelled a domain event is raised and a listener composes the message, but the mailer adapter writes a log entry to disk instead of contacting a real mail service. The recipient address is deliberately not logged.

Domain events are dispatched synchronously within the same request. In a production system these events should be published through an outbox: the event would be written to the database in the same transaction as the state change and relayed to the message broker afterwards by a separate process, guaranteeing that the state change and the event are never out of sync. That has been left out here on purpose, given the time available and the scope of the exercise, in favour of the simpler synchronous dispatch.

## The API

All responses share the same JSON envelope. The HTTP status code already tells the caller whether the request succeeded, so a successful response carries a `data` object (or an array of objects for collections) and an error response carries an `error` object with a machine-readable `code`, a human-readable `message` and, for validation errors, the offending fields. Creating a resource returns `201` with a `Location` header pointing at the new resource. Validation failures return `422`, a malformed JSON body returns `400`, an unknown resource returns `404` and a conflict with the current state (for example not enough seats, or cancelling an already cancelled booking) returns `409`.

The write endpoints are:

| Method | Path | Description |
| --- | --- | --- |
| POST | `/experiences` | Register an experience |
| POST | `/experiences/{experienceId}/sessions` | Create a session for an experience |
| POST | `/sessions/{sessionId}/bookings` | Book seats for a session |
| POST | `/bookings/{bookingId}/cancellation` | Cancel a booking |

The read endpoints are:

| Method | Path | Description |
| --- | --- | --- |
| GET | `/experiences` | List all experiences |
| GET | `/experiences/{experienceId}` | Get a single experience |
| GET | `/experiences/{experienceId}/sessions` | List the sessions of an experience |
| GET | `/sessions/{sessionId}` | Get a single session |
| GET | `/sessions/{sessionId}/bookings` | List the bookings of a session |
| GET | `/bookings/{bookingId}` | Get a single booking |

Cancellation is modelled as creating a cancellation on a booking (`POST /bookings/{id}/cancellation`) rather than as a `DELETE`, because cancelling does not remove the booking: it transitions its state to cancelled, releases the seats back to the session and returns the affected resource.

As an example, registering an experience:

    POST /experiences
    { "providerId": "01ARZ3NDEKTSV4RRFFQ69G5FAV", "title": "Guided city tour", "description": "A two-hour walk." }

responds with `201 Created`, a `Location: /experiences/{id}` header and:

    { "data": { "id": "01J9Z3K7P2QW8V6M4T0XR5E9AB" } }

Since providers and users are not modelled, the `providerId` in the create-experience request and the `userId` in the book-seats request are arbitrary ULIDs supplied by the caller, as the brief allows.

The domain rules enforced by the API are the ones described in the brief: a session cannot be created on a date that already has another session for the same experience, nor in the past. A booking cannot be made for a session that has already started. A booking can only be cancelled up to twenty-four hours before the session starts. A cancelled booking cannot be cancelled again. And cancelling a confirmed booking returns its seats to the session.

## Testing

The test suite covers the domain, the application handlers, the Doctrine repositories and the HTTP endpoints. Run it with `make test`.

The integration and functional tests run against the running development database rather than a separate one. Each test opens a transaction in its setup and rolls it back on teardown, so nothing is left behind and the tests do not interfere with each other or with existing data. Because of this, the containers must be up and the schema must have been created (`make create-db`) before running the tests.

## Manual testing with Bruno

A Bruno collection is included under the `.bruno` directory for exercising the API by hand. It is ready to use: select the `Local` environment (which points at `http://localhost:8080`) and run the requests in order. Each request stores the id returned by the server into a variable that the following requests reuse, and the session date is generated dynamically, so the full flow (register an experience, create a session, book seats, cancel the booking and read everything back) works without editing anything.

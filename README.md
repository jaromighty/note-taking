# Team Notes API

A lightweight RESTful backend for a collaborative note-taking service designed for small teams. Built with **Laravel** and **PHP**.

## 🚀 Quick Start

This project uses SQLite for zero-config local development.

1. **Clone and Install**
   ```bash
   git clone <your-repo-url>
   cd team-notes-api
   composer install
   ```
   
2. **Environment Setup**
    ```bash
    cp .env.example .env
    php artisan key:generate
    # Ensure DB_CONNECTION=sqlite in .env and create an empty database.sqlite file if needed
    touch database/database.sqlite
    ```
3. **Run Migrations**
    ```bash
    php artisan migrate
    ```
   
4. **Start the Server**
    ```bash
    php artisan serve
    ```
   
The API will be available at http://localhost:8000/api.

## 📡 API Endpoints
All endpoints require an `Authorization: Bearer {token}` header (except login/register).

| Method | Endpoint                | Description                                     |
|:-------|-------------------------|-------------------------------------------------|
| POST   | /api/register           | Register a new user                             |
| POST   | /api/login              | Login and receive a token                       |
| POST   | /api/logout             | Revoke current token                            |
| POST   | /api/teams              | Create a new team (creator joins automatically) |
| GET    | /api/teams              | List teams the current user belongs to          |
| POST   | /api/teams/{id}/members | Add a user to a team (Admin only)               |
| GET    | /api/teams/{id}/notes   | List all notes in a team                        |
| POST   | /api/teams/{id}/notes   | Create a new note in a team                     |
| GET    | /api/notes/{id}         | View a specific note                            |
| PUT    | /api/notes/{id}         | Update a note                                   |
| DELETE | /api/notes/{id}         | Delete a note                                   |

### Example: Creating a Note
```bash
curl -X POST http://localhost:8000/api/teams/1/notes \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{"title": "Meeting Notes", "body": "Discuss Q4 goals"}'
```

### Example: Unauthorized Access (Expected 403)
Attempting to access a note from a team you do not belong to returns `403 Forbidden`

## 🧠 Design Decisions & Tradeoffs
This section outlines the architectural choices made to balance speed of delivery, security, and scalability within the
constraints of the short-term assignment.

### 1. Team Visibility Model
**Decision:** All notes within a team are visible to all team members. **Reasoning:** The prompt specifies the service
is "shared amongst teams." I interpreted this as a collaborative workspace where transparency is the default.
Implementing granular "private notes" would require additional flags (`is_private`) and complex permission logic which
were out of scope for this timeframe. **Future Work:** Add an `is_private` boolean to the `notes` table and update the
`NotePolicy` to check ownership if private notes are required.

### 2. Authentication Strategy
**Decision:** Used *Laravel Sanctum* for token-based authentication. **Reasoning:** Compared to *Laravel Passport*
(full OAuth2 stack), Sanctum is significantly lighter, easier to configure, and perfectly suited for single-page
applications or mobile backends. It reduces setup time by ~30 minutes while providing robust security.

### 3. Database & Storage
**Decision:** SQLite for development/demo; easily swappable for PostgreSQL/MySQL. **Reasoning:** SQLite requires no
server installation or configuration, allowing immediate testing. The schema uses standard SQL types, ensuring a
seamless migration to a production RDBMS with zero code changes.

### 4. Authorization Implementation
**Decision:** Middleware-based team checks. **Reasoning:** While Laravel Policies offer a more structured approach for
large apps, a dedicated middleware (`CheckTeamMembership`) was chosen here for brevity and clarity in a small codebase.
It effectively prevents users from accessing resources outside their team context.

### 5. Scope Exclusions
To ensure a working, bug-free delivery within 6 hours, the following were intentionally excluded:
- **Soft Deletes:** Hard deletes are used to simplify the schema. Soft deletes can be added via the SoftDeletes trait later.
- **File Attachments:** Notes are text-only to avoid storage driver complexity and security risks (XSS/Malware).
- **Email Invites:** Team member addition is handled via direct User ID for simplicity. In production, this would trigger an email invitation flow.
- **Search Engine:** Basic SQL LIKE queries are sufficient for small datasets; full-text search engines (Meilisearch/Elasticsearch) were deemed unnecessary for this MVP.

## 🛡️ Security Considerations
- **Input Validation:** All incoming data is validated using Laravel Form Requests to prevent injection attacks.
- **CSRF Protection:** Disabled for API routes (standard practice for token-based auth); CSRF tokens are enforced for web routes.
- **Rate Limiting:** Basic rate limiting is enabled on API routes to prevent brute-force attacks.

## 🧪 Testing
Run the test suite to verify authorization logic:
```bash
php artisan test
```

Key tests include:
1. User A creates a note
2. User B (same team) successfully reads User A's note.
3. User C (different team) fails to read User A's note (403).

## 🤝 Contributing
This is a demonstration project. For the interview review, please refer to the `Design Decisions` section above for
context on the implementation.

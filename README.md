# JWT Authentication API (PHP + MySQLi)

This project implements IPT Integrative Programming and Technologies 1 Activity 2 requirements:

- POST `/register`
- POST `/login`
- GET `/protected` (JWT required)
- POST `/logout`
- GET `/protected` after token removal/revocation

## Requirements

- PHP 8+
- Composer
- XAMPP/MySQL on port `3307`
- Postman

## Setup

1. Install dependencies:

   ```bash
   composer install
   ```

2. Create environment file:

   - Copy `.env.example` to `.env`
   - Update values as needed

3. Import database schema:

   - Run `database.sql` in phpMyAdmin/MySQL

4. Start local server:

   ```bash
   php -S localhost:8000 -t public
   ```

## Postman Test Order

1. `POST http://localhost:8000/register`
   - JSON body:
   ```json
   {"name":"Mike","email":"mike.sordilla@nmsc.edu.ph","password":"12345678"}
   ```

2. `POST http://localhost:8000/login`
   - JSON body:
   ```json
      
   ```
   - Copy `data.token`
      //token IiOjQsImVtYWlsIjoibWlrZS5zb3JkaWxsYUBubXNjLmVkdS5waCIsImlhdCI6MTc3NjI1NTYyNywiZXhwIjoxNzc2MjU5MjI3LCJqdGkiOiJjOTMxZWM1ZWNlYWU2YWU0ZDQ2MzBmYzk3M2VjM2Y2YyJ9.o5fbjdD3-Pai7RbPwuFtA50eaNYivvYtbCCKycGcecM
3. `GET http://localhost:8000/protected`
   - Header: `Authorization: Bearer <IiOjQsImVtYWlsIjoibWlrZS5zb3JkaWxsYUBubXNjLmVkdS5waCIsImlhdCI6MTc3NjI1NTYyNywiZXhwIjoxNzc2MjU5MjI3LCJqdGkiOiJjOTMxZWM1ZWNlYWU2YWU0ZDQ2MzBmYzk3M2VjM2Y2YyJ9.o5fbjdD3-Pai7RbPwuFtA50eaNYivvYtbCCKycGcecM>`

4. `POST http://localhost:8000/logout`
   - Header: `Authorization: Bearer <token>`

5. `GET http://localhost:8000/protected`
   - Remove token header or use revoked token
   - Expected: `401 Unauthorized`

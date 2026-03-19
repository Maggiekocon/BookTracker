CREATE DATABASE IF NOT EXISTS BookTrackerDB;
USE BookTrackerDB;

-- ======================
-- SAFE RESET
-- ======================
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS saved;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS book_author;
DROP TABLE IF EXISTS authors;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ======================
-- USERS
-- ======================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ======================
-- BOOKS
-- ======================
CREATE TABLE books (
    isbn VARCHAR(13) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    cover_url VARCHAR(500),
    genre VARCHAR(100),
    page_count INT,
    average_rating DECIMAL(3,2),
    buy_link VARCHAR(500)
) ENGINE=InnoDB;

-- ======================
-- AUTHORS
-- ======================
CREATE TABLE authors (
    author_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(225) NOT NULL
) ENGINE=InnoDB;

-- ======================
-- BOOK ↔ AUTHOR (MANY-TO-MANY)
-- ======================
CREATE TABLE book_author (
    isbn VARCHAR(13) NOT NULL,
    author_id INT NOT NULL,

    PRIMARY KEY (isbn, author_id),

    FOREIGN KEY (isbn) REFERENCES books(isbn)
        ON DELETE CASCADE ON UPDATE CASCADE,

    FOREIGN KEY (author_id) REFERENCES authors(author_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ======================
-- REVIEWS
-- ======================
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    isbn VARCHAR(13) NOT NULL,
    comment TEXT NOT NULL,
    rating TINYINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    FOREIGN KEY (isbn) REFERENCES books(isbn)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ======================
-- SAVED BOOKS
-- ======================
CREATE TABLE saved (
    user_id INT NOT NULL,
    isbn VARCHAR(13) NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    category VARCHAR(20) NOT NULL,

    PRIMARY KEY (user_id, isbn),

    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    FOREIGN KEY (isbn) REFERENCES books(isbn)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (username, password_hash, first_name, last_name, email) VALUES
('jdoe', 'hash1', 'John', 'Doe', 'jdoe@email.com'),
('asmith', 'hash2', 'Alice', 'Smith', 'asmith@email.com'),
('bwayne', 'hash3', 'Bruce', 'Wayne', 'bwayne@email.com'),
('ckent', 'hash4', 'Clark', 'Kent', 'ckent@email.com'),
('dprince', 'hash5', 'Diana', 'Prince', 'dprince@email.com');

INSERT INTO books (isbn, title, description, cover_url, genre, page_count, average_rating, buy_link) VALUES
('9780061122415', 'The Alchemist', 'A journey of self-discovery.', '', 'Fiction', 208, 4.5, ''),
('9780439708180', 'Harry Potter and the Sorcerer''s Stone', 'A young wizard begins his journey.', '', 'Fantasy', 309, 4.8, ''),
('9780060850524', 'Brave New World', 'A dystopian future society.', '', 'Sci-Fi', 268, 4.2, ''),
('9780451524935', '1984', 'Totalitarian regime surveillance.', '', 'Dystopian', 328, 4.7, ''),
('9780062315007', 'The Hobbit', 'A hobbit goes on an adventure.', '', 'Fantasy', 310, 4.6, '');

INSERT INTO authors (name) VALUES
('Paulo Coelho'),
('J.K. Rowling'),
('Aldous Huxley'),
('George Orwell'),
('J.R.R. Tolkien');

INSERT INTO book_author (isbn, author_id) VALUES
('9780061122415', 1),
('9780439708180', 2),
('9780060850524', 3),
('9780451524935', 4),
('9780062315007', 5);

INSERT INTO book_author (isbn, author_id) VALUES
('9780439708180', 1); -- adds second author to Harry Potter (for testing)

INSERT INTO reviews (user_id, isbn, comment, rating) VALUES
(1, '9780061122415', 'Amazing and inspiring.', 5),
(2, '9780439708180', 'Loved the magic!', 5),
(3, '9780060850524', 'Very thought-provoking.', 4),
(4, '9780451524935', 'A bit scary but great.', 5),
(5, '9780062315007', 'Fun adventure.', 4);

INSERT INTO saved (user_id, isbn, category) VALUES
(1, '9780061122415', 'favorites'),
(2, '9780439708180', 'reading'),
(3, '9780060850524', 'wishlist'),
(4, '9780451524935', 'favorites'),
(5, '9780062315007', 'reading');
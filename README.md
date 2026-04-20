# BookTracker Project Setup

## Installation Instructions

To run this project you will need to do the following things:

1. Download XAMP and place this project in htdocs folder.  
2. Create a database in MySQL, using the “db_script.”  
3. Create a Google books API key (if not provided by the developer).  
4. Make your own `.env` file and use composer to ensure secrets from the file auto load into your project.  

The following instructions will walk you through how to do each of these parts.

---

## Part One: Download XAMPP

1. Go to the official XAMPP website:  
   https://www.apachefriends.org  

2. Download the version for your operating system (Windows/macOS/Linux).  

3. Run the installer and follow the setup steps (default settings are fine).  

4. After installation, open the XAMPP Control Panel.  

5. Start the following services:  
   - Apache  
   - MySQL  

6. Navigate to your XAMPP installation folder:  
   - Windows: `C:\xampp\htdocs`  
   - macOS: `/Applications/XAMPP/htdocs`  

7. Copy or move this project folder into the htdocs directory.  

---

## Part Two: Run Database Script

1. Open your browser and go to:  
   http://localhost/phpmyadmin  

2. In phpMyAdmin:  
   - Click “New” in the left sidebar.  
   - Enter database name: `booktrackerdb`  
   - Click Create  

3. Import the provided database script 'db_script.sql':  
   - Click on the newly created database (`booktrackerdb`)  
   - Go to the Import tab  
   - Click Choose File  
   - Select the file: `db_script.sql`   
   - Click Go  

4. Wait for confirmation that the tables were created successfully.  

---

## Part Three: Create Google Books API Key

1. Go to the Google Cloud Console:  
   https://console.cloud.google.com/  

2. Sign in with your Google account.  

3. Create a new project:  
   - Click the project dropdown (top bar)  
   - Click New Project  
   - Give it a name (e.g., `BookTracker`)  
   - Click Create  

4. Enable the Google Books API:  
   - In the search bar, type “Books API”  
   - Click Google Books API  
   - Click Enable  

5. Create an API Key:  
   - Go to APIs & Services → Credentials  
   - Click Create Credentials → API Key  
   - Copy the generated API key  

6. (Optional but recommended) Restrict the key:  
   - Click Restrict Key  
   - Limit usage to HTTP referrers or localhost  

7. Save your API key — you’ll add it to your `.env` file later.  

---

## Part Four: Set up .env

### 1. Install PHP
*If not already installed*

#### Windows
1. Download PHP from the official website.  
2. Choose the Thread Safe ZIP version.  
3. Extract to `C:\php`.  
4. Add `C:\php` to your system PATH.  
5. Verify installation:
php -v

#### macOS
Install using Homebrew:

brew install php

Verify:

php -v

---

### 2. Install Composer

#### Windows
1. Download Composer-Setup.exe.  
2. Run the installer and follow instructions.  
3. Verify:
composer -V


#### macOS / Linux

php -r "copy('https://getcomposer.org/installer
', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer


Verify:

composer -V


---

### 3. Create a .env File


touch .env


Add the following:


API_KEY='your_key'

DB_HOST=localhost

DB_USER=root

DB_PASS=''

DB_NAME=booktrackerdb


---

### 4. Install dotenv Library


composer require vlucas/phpdotenv


---

### 5. Ensure New Files are Ignored
*Optional, recommended if you plan to share this project*

Create `.gitignore`:

touch .gitignore


Add:

.env
/vendor/
composer.json
composer.lock


---

## Final Notes

- Make sure Apache and MySQL are running in XAMPP before testing the project.  
- Ensure your `.env` file is correctly configured before running the app.  
- Never commit your `.env` file to version control.  

---

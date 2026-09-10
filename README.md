# 🎓 Lumina Academy — AI-Powered School Management System

> A modern, AI-powered School Management System built with **Laravel 12**, **Livewire**, **Tailwind CSS 4**, **MySQL**, and a **local Large Language Model (Ollama + Qwen3)** to streamline academic, administrative, financial, and learning workflows for educational institutions.

![Laravel](https://img.shields.io/badge/Laravel-12-red)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4-38BDF8)
![Livewire](https://img.shields.io/badge/Livewire-3-purple)
![License](https://img.shields.io/badge/License-MIT-green)

---

# Table of Contents

* [Overview](#overview)
* [Why Lumina Academy?](#why-lumina-academy)
* [Key Features](#key-features)
* [AI Material Explainer](#ai-material-explainer)
* [User Roles](#user-roles)
* [Technology Stack](#technology-stack)
* [Project Architecture](#project-architecture)
* [Database Overview](#database-overview)
* [Project Structure](#project-structure)
* [Screenshots](#screenshots)
* [Installation](#installation)
* [Running the Project](#running-the-project)
* [Testing](#testing)
* [API Endpoints](#api-endpoints)
* [Future Improvements](#future-improvements)
* [License](#license)
* [Author](#author)

---

# Overview

**Lumina Academy** is a complete web-based School Management System developed as a **Bachelor's Final Year Project**.

The application centralizes every major school operation into one platform, including:

* Student registration
* Course management
* Scheduling
* Attendance
* Financial management
* Homework & learning resources
* Messaging
* Notifications
* Parent monitoring
* AI-assisted learning

Unlike traditional school management systems, Lumina Academy integrates a **local AI assistant** powered by **Ollama** running **Qwen3**, allowing students to automatically generate summaries, vocabulary lists, and quizzes from teacher-uploaded learning materials without sending educational data to external AI providers.

---

# Why Lumina Academy?

Many language schools still manage daily operations using spreadsheets, paper records, messaging applications, or disconnected software.

This creates problems such as:

* Duplicate data
* Poor communication
* Difficult attendance tracking
* Manual tuition management
* Limited student engagement
* No intelligent learning assistance

Lumina Academy solves these issues through a centralized platform where every user accesses only the tools relevant to their role while benefiting from real-time communication and AI-powered educational features.

---

# Key Features

## Academic Management

* Language Programs
* Courses
* Student Enrollment
* Group Management
* Timetables
* Attendance
* Homework
* Course Materials
* Classroom Management
* Teacher Assignment

---

## Financial Management

* Tuition Payments
* Scholarships & Discounts
* Parent Payment Tracking
* Student Financial Dashboard
* Employee Payments
* Payment History
* Receipt Generation (PDF)

---

## Communication

* Private Messaging
* Notifications
* Real-Time Updates using Laravel Reverb
* Parent–Teacher Communication

---

## Authentication & Security

* Laravel Fortify
* Laravel Sanctum
* Role-Based Access Control
* Spatie Permissions
* Protected Routes
* Account Approval Workflow

---

## AI Features

* Automatic PDF text extraction
* Local LLM processing
* Resource summaries
* Vocabulary extraction
* Quiz generation
* Privacy-friendly AI
* No API costs

---

# AI Material Explainer

One of Lumina Academy's flagship features is the **AI Material Explainer**, designed to help students better understand uploaded learning resources.

## Workflow

```text
Teacher uploads PDF
        │
        ▼
smalot/pdfparser extracts text
        │
        ▼
AI Service Layer
        │
        ▼
Ollama
(Qwen3:4b)
        │
        ▼
AI generates:

• Summary
• Vocabulary
• Quiz Questions
        │
        ▼
Student opens AI Explainer
```

### Current Features

* PDF text extraction
* AI-generated summaries
* Vocabulary extraction
* Quiz generation
* Streaming responses

### Planned AI Features

* Personal Study Coach
* AI Lesson Planner
* Exam Preparation Assistant
* Writing Feedback
* Learning Risk Prediction
* Personalized Revision Plans

---

# User Roles

## Administrator

* Dashboard
* User Approval
* Employee Management
* Program Management
* Course Management
* Classroom Management
* Timetable Management
* Employee Payments
* Notifications
* Messaging
* System Administration

---

## Secretary

* Registration Management
* Student Enrollment
* Group Management
* Teacher Assignment
* Tuition Payments
* Financial Tracking
* Notifications
* Messaging
* Timetable
* Student Search

---

## Teacher

* Dashboard
* Timetable
* Attendance
* Student Evaluation
* Homework
* Learning Resources
* AI Processing
* Messaging
* Notifications
* Payment History

---

## Student

* Dashboard
* Academic Progress
* Attendance
* Timetable
* Learning Materials
* AI Material Explainer
* Financial Dashboard
* Scholarship Management
* Messaging
* Notifications
* Profile Settings

---

## Parent

* Linked Children Dashboard
* Financial Monitoring
* Payment History
* Academic Monitoring
* Messaging
* Notifications
* Child Resources
* Scholarship Activation

---

## Visitor

* Landing Page
* Browse Programs
* Browse Courses
* Registration
* Reviews
* Testimonials

---

# Technology Stack

## Backend

* PHP 8.2+
* Laravel 12
* MySQL / MariaDB
* Eloquent ORM
* Laravel Fortify
* Laravel Sanctum
* Laravel Reverb
* Spatie Laravel Permission
* smalot/pdfparser
* Ollama
* Qwen3:4b

---

## Frontend

* Blade
* Tailwind CSS 4
* Livewire
* Flux UI
* Vite
* Laravel Echo
* Axios

---

## Development Tools

* Composer
* npm
* Pest
* PHPUnit
* Laravel Pint
* Git

---

# Important Packages

## Composer

| Package                   | Purpose                 |
| ------------------------- | ----------------------- |
| laravel/framework         | Laravel Framework       |
| laravel/fortify           | Authentication          |
| laravel/sanctum           | API Authentication      |
| laravel/reverb            | Real-Time Communication |
| spatie/laravel-permission | Roles & Permissions     |
| smalot/pdfparser          | PDF Text Extraction     |
| livewire/livewire         | Interactive Components  |
| livewire/flux             | UI Components           |
| pestphp/pest              | Testing                 |
| laravel/pint              | Code Formatting         |

---

## npm

| Package           | Purpose              |
| ----------------- | -------------------- |
| vite              | Asset Bundling       |
| @tailwindcss/vite | Tailwind Integration |
| laravel-echo      | Real-Time Client     |
| pusher-js         | Reverb Support       |
| axios             | HTTP Client          |

---

# Project Architecture

```text
Browser
    │
Blade + Livewire
    │
Controllers
    │
Application Services
    │
Business Logic
    │
Models (Eloquent)
    │
MySQL Database
```

### AI Architecture

```text
Student
    │
AI Material Explainer
    │
AI Controller
    │
AI Service
    │
Ollama Provider
    │
Qwen3:4b
```

---

# Database Overview

Core entities include:

* Users
* Roles
* Permissions
* Programs
* Courses
* Groups
* Classrooms
* Timetables
* Attendance
* Evaluations
* Homework
* Learning Resources
* Messages
* Notifications
* Payments
* Scholarships
* Parents
* Students
* Teachers
* Secretaries

---

# Project Structure

```text
app
├── Http
├── Models
├── Policies
├── Providers
├── Services
├── Support
├── Jobs
├── Notifications

database
├── migrations
├── seeders

resources
├── views
│   ├── admin
│   ├── secretary
│   ├── teacher
│   ├── student
│   ├── parent
│   ├── visitor
│   ├── layouts
│   └── components

routes
├── web.php
├── api.php

tests
├── Feature
├── Unit
```

---

# Screenshots

> *(Replace with your own screenshots.)*

| Landing Page              | Student Dashboard                      |
| ------------------------- | -------------------------------------- |
| ![](docs/images/home.png) | ![](docs/images/student-dashboard.png) |

| Teacher Dashboard                      | AI Material Explainer             |
| -------------------------------------- | --------------------------------- |
| ![](docs/images/teacher-dashboard.png) | ![](docs/images/ai-explainer.png) |

| Financial Dashboard          | Timetable                      |
| ---------------------------- | ------------------------------ |
| ![](docs/images/finance.png) | ![](docs/images/timetable.png) |

---

# System Requirements

* PHP 8.2+
* Composer
* Node.js
* npm
* MySQL or MariaDB
* Git
* Ollama
* PHP Extensions:

  * pdo_mysql
  * mbstring
  * xml
  * zip

---

# Installation

```bash
# Clone repository
git clone <repository-url>

cd lumina-academy

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database credentials inside .env

# Run migrations
php artisan migrate

# Seed demo data (optional)
php artisan db:seed

# Build frontend assets
npm run build
```

---

# Running the Project

## Development

```bash
php artisan serve
```

```bash
npm run dev
```

---

## Start Reverb

```bash
php artisan reverb:start
```

---

## Start Ollama

```bash
ollama serve
```

Run the model:

```bash
ollama run qwen3:4b
```

---

# Testing

The project uses **Pest** and **PHPUnit**.

Run all tests:

```bash
php artisan test
```

Example test coverage:

* Authentication
* Authorization
* Enrollment
* Attendance
* Financial Workflows
* API Endpoints
* AI Services

---

# API Endpoints

## Authentication

```
POST /api/login
POST /api/logout
GET  /api/me
```

---

## Student

```
GET /api/student/timetable
```

---

## Parent

```
GET /api/parent/timetable
```

---

## Admin

```
GET /api/admin/timetables
```

---

# Future Improvements

* AI Study Coach
* AI Chat Assistant
* OCR Support for Scanned PDFs
* AI Lesson Recommendations
* Mobile Application
* Multi-School Support
* Analytics Dashboard
* AI Learning Risk Prediction
* Speech Recognition
* Voice-Based Learning

---

# License

This project is licensed under the **MIT License**.

---

# Author

**Chihab Hamdane**

Bachelor's in Computer Science (Information Systems)

Backend Developer • Laravel Developer • AI Enthusiast

GitHub: *(add your profile)*

LinkedIn: *(add your profile)*

Email: *(add your email)*

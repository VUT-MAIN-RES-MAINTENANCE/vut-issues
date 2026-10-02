
# VUT Residence Maintenance System

A web-based residence maintenance system for managing student maintenance requests, maintenance staff and administrators.

The system allows students to report residence maintenance problems, maintenance staff to manage assigned issues and administrators to monitor and manage the overall maintenance process.

## Project Status

The project is currently under development.

Authentication and role-based access have been implemented. The Student, Maintenance Staff and Admin interfaces are being developed separately by team members.

## Technologies

* PHP
* HTML5
* CSS3
* JavaScript
* JSON
* PHP Sessions

## Project Structure

```text
vut-issues/
│
├── index.php
│
├── admin/
│   ├── index.php
│   ├── activities.php
│   ├── issues.php
│   ├── login.php
│   ├── maintenance-staff.php
│   ├── settings.php
│   └── students.php
│
├── assets/
│   ├── css/
│   ├── images/
│   └── js/
│
├── data/
│   ├── activities.json
│   ├── admins.json
│   ├── maintenance_requests.json
│   ├── settings.json
│   ├── staff.json
│   └── students.json
│
├── includes/
│   ├── auth.php
│   ├── config.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── json.php
│
├── maintenance/
│   ├── index.php
│   ├── assigned-issues.php
│   ├── issue-details.php
│   ├── login.php
│   └── profile.php
│
├── pictures/
│   └── issues/
│
├── student/
│   ├── index.php
│   ├── login.php
│   ├── my-issues.php
│   ├── profile.php
│   ├── register.php
│   └── report-issue.php
│
└── README.md
```

## User Roles

### Student

Students will be able to:

* Register an account
* Log in and log out
* Report maintenance issues
* Select an issue category
* Provide residence and room information
* Describe the problem
* Upload pictures of issues
* View their submitted issues
* Track issue status
* Manage their profile

### Maintenance Staff

Maintenance staff will be able to:

* Log in and log out
* View assigned maintenance issues
* View issue details
* Update issue status
* Add notes
* Mark issues as completed
* Upload completion pictures
* Manage their profile

### Administrator

Administrators will be able to:

* Log in and log out
* View students
* View maintenance staff
* View all maintenance requests
* Assign issues to maintenance staff
* Change issue assignments
* Monitor issue statuses
* View system activities
* Manage system settings

## Authentication

The system uses PHP sessions and role-based authentication.

After login, users are redirected to their specific interface:

```text
Student
    ↓
student/index.php

Maintenance Staff
    ↓
maintenance/index.php

Admin
    ↓
admin/index.php
```

Each interface is protected so users cannot access another role's interface.

For example:

* Students cannot access the Admin interface.
* Students cannot access the Maintenance Staff interface.
* Maintenance Staff cannot access the Student interface.
* Maintenance Staff cannot access the Admin interface.
* Admins cannot access the Student or Maintenance Staff interfaces.

## Data Storage

The system uses JSON files to store application data.

```text
data/
├── activities.json
├── admins.json
├── maintenance_requests.json
├── settings.json
├── staff.json
└── students.json
```

The shared JSON functionality is handled through:

```text
includes/json.php
```

Shared application functions are handled through:

```text
includes/functions.php
```

Authentication and session management are handled through:

```text
includes/auth.php
```

## Maintenance Requests

A maintenance request can contain information such as:

* Student
* Residence
* Room
* Issue category
* Issue description
* Issue pictures
* Date reported
* Assigned maintenance staff
* Issue status
* Staff notes
* Completion information

Possible statuses can include:

```text
Pending
Assigned
In Progress
Completed
```

## Pictures

Issue pictures are stored in:

```text
pictures/issues/
```

The JSON data should store the relevant picture filename or path rather than storing the actual image inside the JSON file.

## Security

The system should follow basic security practices, including:

* Password hashing
* Password verification
* PHP sessions
* Role-based access protection
* Input validation
* Output escaping
* Protected pages
* Secure file uploads
* No plain-text passwords
* Prevention of unauthorized access

## Team Development

The project uses Git branches so each team member can work independently.

```text
main
├── andzani
├── hlayiseko
├── risima
└── mikhenso
```

### Main Branch

The `main` branch contains the shared and tested version of the project.

Team members should not directly develop on `main`.

### Development Workflow

Before starting work:

```bash
git checkout main
git pull origin main
```

Switch to your branch:

```bash
git checkout <your-branch>
```

After making changes:

```bash
git add .
git commit -m "Describe your changes"
git push origin <your-branch>
```

Changes should be tested before being merged into `main`.

## Development Rules

* Keep the existing project structure organized.
* Work mainly inside your assigned area.
* Do not unnecessarily modify another member's files.
* Check `includes/` before creating duplicate functions.
* Do not duplicate authentication logic.
* Do not change shared authentication files without communicating with the team.
* Preserve the existing frontend design.
* Keep JSON files valid.
* Test PHP pages before committing.
* Check for broken links.
* Check CSS and JavaScript paths.
* Test authentication and role protection.
* Do not commit passwords or sensitive information.

## Current Development Phase

The project is currently being developed in stages.

### Completed

* Project structure
* PHP conversion
* JSON data structure
* Authentication
* Registration
* Login
* Logout
* PHP sessions
* Role-based access
* Role-based redirects
* Basic protected interfaces

### In Development

* Student maintenance interface
* Maintenance staff interface
* Admin interface
* Maintenance request management
* Issue assignment
* Issue status management
* Activity logging
* Profile management
* Picture uploads
* System settings

## Goal

The goal of the VUT Residence Maintenance System is to provide a simple platform where students can report residence maintenance problems and where maintenance staff and administrators can manage those problems efficiently.

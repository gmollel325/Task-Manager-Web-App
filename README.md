# Task-Manager-Web-App
The task manager web application is a simple, server-rendered system built to let users create, organize, and track tasks through a clean web interface. 

**Author Information**
Name: Gift Mollel
University: Mzumbe University
Programme: Bachelor of Science in Information, Technology and Systems (BSc. ITS)
Year of Study: Second Year
Course: Introduction to Web Programming (CSS 212)
Project: Task Manager Web Application (Individual Assignment)

**Project Overview**
The Task Manager Web Application is a simple, server-rendered system built to let users create, organize, and track tasks through a clean web interface. It uses PHP for server logic, a MySQL database for persistent storage, standard HTML and CSS for the user interface, and minimal JavaScript for client-side validation. The design focuses on straightforward functionality: user accounts, task CRUD operations, filtering and searching, and a small admin area for global management.

**Application Structure**
The application is organized as a collection of PHP pages that each handle a single responsibility: authentication, task listing and filtering, task creation and editing, and administrative actions. Database access is performed through a secure database library with prepared statements to avoid SQL injection, and sessions are used to track logged-in users and their roles. Tasks are stored with attributes such as title, description, due date, priority, category, status, and an approval field that supports administrative review workflows.

**User Flow**
After registering and signing in, the user sees a dashboard where they can view, search, filter, and sort their tasks. They can add a new task by filling a simple form with title, description, category, priority, and due date. Users can edit, mark as done or pending, delete, or export their tasks to a CSV file. The dashboard also shows task counts and completion percentage, and it works well on small screens.

**Administrator Features**
Admins can manage all users and all tasks. They can promote or demote users, delete accounts, edit any task, and change approval status. All admin actions are recorded in an activity log. The first registered user automatically becomes an admin if no admin exists, which makes initial setup easy.

**Security and Deployment**
Passwords are hashed, database queries use prepared statements to prevent SQL injection, and outputs are escaped to reduce XSS risks. For production, add CSRF protection, move database credentials to environment variables, enable HTTPS, and implement rate limiting with stronger password rules.

**How to Run the System**
Create the database and tables using the provided schema, then configure the database connection for your environment. Place the application under a PHP-enabled web server, and users can register and start using the task manager.

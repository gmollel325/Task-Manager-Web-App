# 👋 Hi, I'm Gift Mollel

🎓 **BSc. Information Technology & Systems (ITS)** | Second Year  
📍 Mzumbe University, Morogoro, Tanzania  

## 🚀 About Me

I am a second-year student passionate about web development. This is where I share my coursework projects and personal learning journey.

## 📂 Current Project: Task Manager Web App

A PHP & MySQL web application built for my **Introduction to Web Programming (CSS 221)** course.

## 📂 Current Project: Task Manager Web App

A simple, server-rendered system built to let users create, organize, and track tasks through a clean web interface. built for my **Introduction to Web Programming (CSS 221)** course at Mzumbe University.

**Tech Stack:** PHP, MySQL, HTML, CSS, JavaScript

### ✨ Features

**For Regular Users:**
- User registration and secure login
- Create, edit, and delete tasks
- Mark tasks as done or pending
- Search, filter, sort, and export tasks to CSV
- View task counts and completion percentage
- Responsive design works on mobile and tablet screens

**For Administrators:**
- Manage all users and all tasks
- Promote or demote users
- Delete any user account
- Edit any task and adjust approval status
- All admin actions recorded in an activity log
- First registered user automatically becomes admin (no manual database setup needed)

### 🔒 Security Features

- **Password Protection:** User passwords are stored using a secure hashing function
- **SQL Injection Prevention:** All database queries use prepared statements
- **XSS Protection:** Output is properly escaped before displaying in the browser
- **Session Management:** User sessions track login status and user roles

### 🚀 Production Recommendations

For deploying this application in a real environment, the following are recommended:
- Add CSRF protection for all forms
- Move database credentials to environment variables (not in web-accessible files)
- Enable HTTPS encryption
- Implement rate limiting
- Enforce stronger password rules

### 🛠️ How to Run

1. Create the database and tables using the provided SQL schema
2. Configure the database connection for your environment
3. Place all files under a PHP-enabled web server
4. Users can register and start using the task manager immediately

## 📫 Connect With Me

- GitHub: [GMollel](https://github.com/gmollel325)
- Email: [giftmollel325@gmail.com]

*This README will grow as I learn more!... Thank You!!*

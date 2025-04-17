<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Notice Board</title>
    <link rel="stylesheet" href="/university_notice_board/css/public.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="nav-container">
            <nav class="main-nav">
                <a href="/university_notice_board/index.php" class="site-title">University Notice Board</a>
                <ul class="nav-links">
                    <li><a href="/university_notice_board/index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="/university_notice_board/academic.php"><i class="fas fa-graduation-cap"></i> Academic</a></li>
                    <li><a href="/university_notice_board/events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                    <li><a href="/university_notice_board/examination.php"><i class="fas fa-file-alt"></i> Examination</a></li>
                    <li><a href="/university_notice_board/auth/login.php"><i class="fas fa-user"></i> Admin Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section with Search -->
    <section class="hero">
        <div class="hero-content">
            <h1>University Notice Board</h1>
            <p>Stay updated with the latest announcements and notices</p>
            
            <!-- Search Form -->
            <div class="search-container">
                <form action="/university_notice_board/index.php" method="GET" class="search-form">
                    <div class="search-row">
                        <div class="search-group">
                            <input type="text" name="search" class="search-input" placeholder="Search notices...">
                        </div>
                        <div class="search-group">
                            <select name="category" class="search-select">
                                <option value="">All Categories</option>
                                <option value="academic">Academic</option>
                                <option value="events">Events</option>
                                <option value="examination">Examination</option>
                                <option value="general">General</option>
                            </select>
                        </div>
                        <div class="search-group">
                            <select name="time" class="search-select">
                                <option value="">Any Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="home-search">
                            <button type="submit" class="search-button">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    
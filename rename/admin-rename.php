<?php
if (is_page('myaccount') || $wp->request === "myaccount") {
    $title_parts['title'] = 'My Account';
} elseif ($wp->request === "edit_profile") {
    $title_parts['title'] = 'Edit Profile';
} elseif ($wp->request === "news_post") {
    $title_parts['title'] = 'News Posts';
} elseif ($wp->request === "event_post") {
    $title_parts['title'] = 'Event Posts';
} elseif ($wp->request === "view-user") {
    $title_parts['title'] = 'Student Profile';
} elseif ($wp->request === "view_homework_student") {
    $title_parts['title'] = 'View Homework Student';
}

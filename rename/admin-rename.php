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
} elseif ($wp->request === "homework") {
    $title_parts['title'] = 'Class Assessment';
} elseif ($wp->request === "homeworkroom") {
    $title_parts['title'] = 'Class Assessment Room';
} elseif ($wp->request === "homeworkroom_edit") {
    $title_parts['title'] = 'Assessment Room Edit';
} elseif ($wp->request === "homeworkroom_details") {
    $title_parts['title'] = 'Assessment Room Details';
} elseif ($wp->request === "view_homework_student") {
    $title_parts['title'] = 'View Assessment Student';
}

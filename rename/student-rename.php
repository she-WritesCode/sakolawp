<?php
if (is_page('myaccount') || $wp->request === "myaccount") {
    $title_parts['title'] = 'My Account';
} elseif ($wp->request === "waiting") {
    $title_parts['title'] = 'User Waiting Approval';
} elseif ($wp->request === "class_routine") {
    $title_parts['title'] = 'Class Routine';
} elseif ($wp->request === "homework") {
    $title_parts['title'] = 'Class Assessment';
} elseif ($wp->request === "homeworkroom") {
    $title_parts['title'] = 'Class Assessment Room';
} elseif ($wp->request === "homeworkroom_details") {
    $title_parts['title'] = 'Peer Review Report';
} elseif ($wp->request === "peer_review") {
    $title_parts['title'] = 'My Peer Review';
} elseif ($wp->request === "peer_review_room") {
    $title_parts['title'] = 'Peer Review Room';
} elseif ($wp->request === "online_exams") {
    $title_parts['title'] = 'Online Exams';
} elseif ($wp->request === "examroom") {
    $title_parts['title'] = 'Exam Room';
} elseif ($wp->request === "exam") {
    $title_parts['title'] = 'Exam';
} elseif ($wp->request === "view_exam_result") {
    $title_parts['title'] = 'Exam Result';
} elseif ($wp->request === "attendance_report") {
    $title_parts['title'] = 'Attendance Report';
} elseif ($wp->request === "report_attendance_view") {
    $title_parts['title'] = 'Attendance Report';
} elseif ($wp->request === "edit_profile") {
    $title_parts['title'] = 'Edit Profile';
} elseif ($wp->request === "news_post") {
    $title_parts['title'] = 'News Posts';
} elseif ($wp->request === "event_post") {
    $title_parts['title'] = 'Event Posts';
} elseif ($wp->request === "my_marks") {
    $title_parts['title'] = 'My Mark';
} elseif ($wp->request === "view_mark") {
    $title_parts['title'] = 'View Mark';
}

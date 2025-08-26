<?php

   // Load configuration files
   require_once('../config/config.php');
   require_once('../config/database.php');

   if ($_SERVER['REQUEST_METHOD'] === 'GET')
   {
    $requestUri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', $requestUri);
    $id = end($parts);

    $query = "SELECT bp.*,
        (SELECT COUNT(*) FROM post_votes WHERE post_id = bp.id AND vote_type = 'like') AS numLikes,
        (SELECT COUNT(*) FROM post_votes WHERE post_id = bp.id AND vote_type = 'dislike') AS numDislikes
        FROM blog_posts AS bp WHERE bp.id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
   }
?>
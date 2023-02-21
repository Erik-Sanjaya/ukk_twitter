<?php session_start();
require "../../db.php";

$search = $_GET['search'];
$query_tags = mysqli_query($database_connection, "SELECT * FROM tags WHERE name LIKE '%$search%'");

$result = [];

while($row = mysqli_fetch_assoc($query_tags)) {
    $tweets = fetch_tweets_by_tag_id($row['id']);
    $tweets_with_comments = fetch_comments_and_tweets($row['id']);
    $result = array_merge($result, $tweets, $tweets_with_comments);
}

/**
 * Function untuk mengambil tweet yang memiliki tag sama berdasarkan table `tag_tweet`
 */
function fetch_tweets_by_tag_id($tag_id) {
    global $database_connection;

    $select_tag_tweet = mysqli_query($database_connection, "SELECT * FROM tag_tweet WHERE tag_id = '$tag_id'");

    $tweets = [];

    while($row = mysqli_fetch_assoc($select_tag_tweet)) {
        $tweet_id = $row['tweet_id'];
        $select_tweet = mysqli_query($database_connection, "SELECT * FROM tweets WHERE id = '$tweet_id'");
        $result_tweet = mysqli_fetch_assoc($select_tweet);
        if(isset($result_tweet)) {
            $result_tweet['user'] = get_username_and_profile($result_tweet['user_id']);
            $result_tweet['comments'] = [];
        }
        $tweets[] = $result_tweet;
    }

    return $tweets;
}

/**
 * Function untuk mengambil `comments` berdasarkan `tag_id` beserta parent `tweet`nya
 */
function fetch_comments_and_tweets($tag_id) {
    global $database_connection;

    $select_tag_comment = mysqli_query($database_connection, "SELECT * FROM tag_comment WHERE tag_id = '$tag_id'");

    $tweets = [];

    while($row = mysqli_fetch_assoc($select_tag_comment)) {
        $comment_id = $row['comment_id'];
        $select_comment = mysqli_query($database_connection, "SELECT * FROM comments WHERE id = '$comment_id'");
        $result_comment = mysqli_fetch_assoc($select_comment);
        if(isset($result_comment)) {
            $tweet_id = $result_comment['tweet_id'];
            $tweet = fetch_tweet_by_id($tweet_id);

            $result_comment['user'] = get_username_and_profile($result_comment['user_id']);

            $tweet['comments'][] = $result_comment;
            $tweets[] = $tweet;

        }
    }
    
    return $tweets;
}

/**
 * Function untuk mengambil `tweet` berdaasarkan `tweet_id`
 */
function fetch_tweet_by_id($tweet_id) {
    global $database_connection;

    $select_tweet = mysqli_query($database_connection, "SELECT * FROM tweets WHERE id = '$tweet_id'");
    $result_tweet = mysqli_fetch_assoc($select_tweet);
    if(isset($result_tweet)) {
        $result_tweet['user'] = get_username_and_profile($result_tweet['user_id']);
    }

    return $result_tweet;
}

/**
 * Function untuk mengambil profil user berdasarkan `user_id`
 */
function get_username_and_profile($user_id) {
    global $database_connection;

    $select_user = mysqli_query($database_connection, "SELECT username, profile_picture FROM users WHERE id = '$user_id'");
    $result_user = mysqli_fetch_assoc($select_user);

    return $result_user;
}

header("HTTP/1.1 200 OK");
echo json_encode($result);
<?php require "./auth_guard.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="./styles/home.css">
    <link rel="stylesheet" href="./styles/modal.css">
    <link rel="stylesheet" href="./styles/global.css">
    <title>Home</title>
</head>
<body>
    <div class="d-flex">
        <nav class="d-flex justify-content-center" style="width: 30vw">
            <div class="d-flex flex-column mt-5 gap-3">
                <a href="home.php">
                    <div class="nav-item selected">Home</div>
                </a>
                <a id="profile-link" href="profile.php">
                    <div class="nav-item">Profile</div>
                </a>
                <a>
                    <div type="button" onclick="logout()" class="nav-item">Log out</div>
                </a>
            </div>
        </nav>
        <div class="border-start border-end border-2" style="width: 40vw; min-height: 100vh;">
            <div class="d-flex justify-content-center flex-column border-bottom border-2">
                <div class="d-flex justify-content-center flex-column mx-5">
                    <textarea class="mt-4" name="tweet" id="tweet" cols="64" rows="6" maxlength="255" placeholder="What's happening?"></textarea>
                    <div class="d-flex justify-content-between m-4">
                        <input type="file" name="image" id="image" accept=".png, .jpg, .jpeg">
                        <button type="button" class="btn btn-primary" onclick="tweet()">Tweet</button>
                    </div>
                </div>
            </div>
            <div id="tweet-container">
                
                
            </div>
        </div>
        <div class="d-flex justify-content-center" style="width: 30vw">
            <div class="d-flex flex-column mt-5 gap-3">
                <div class="d-flex gap-2">
                    <input type="text" name="search" id="search" placeholder="Search" class="form-control">
                    <button class="btn btn-primary" onclick="search()">Search</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Comment Modal -->
    <div class="modal-overlay" id="createCommentModalOverlay">
        <div class="custom-modal" id="createCommentModal">
            <div class="container mt-2">
                <div>
                    <h1>Post a comment</h1>
                    <div class="d-flex justify-content-between">
                        <input type="file" accept=".png, .jpg, .jpeg" class="form-control-file mb-2" name="createCommentImage" id="createCommentImage"></input>
                        <div type="button" style="background-color: rgba(0, 0, 0, 0);" onclick="closeCreateCommentModal()">&times;</div>
                    </div>
                    <textarea class="form-control" name="caption" id="createComment" cols="30" rows="8"></textarea>
                    <button type="button" class="btn btn-danger mt-2 mb-2" onclick="closeCreateCommentModal()">Close</button>
                    <button type="button" class="btn btn-primary mt-2 mb-2" onclick="makeComment()">Post</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Comment Modal -->
    <div class="modal-overlay" id="editCommentModalOverlay">
        <div class="custom-modal" id="editCommentModal">
            <div class="container mt-2">
                <div>
                    <h1>Edit this comment</h1>
                    <div class="d-flex justify-content-between">
                        <input type="file" accept=".png, .jpg, .jpeg" class="form-control-file mb-2" name="editCommentImage" id="editCommentImage"></input>
                        <div type="button" style="background-color: rgba(0, 0, 0, 0);" onclick="closeEditCommentModal()">&times;</div>
                    </div>
                    <textarea class="form-control" name="caption" id="editComment" cols="30" rows="8"></textarea>
                    <button type="button" class="btn btn-danger mt-2 mb-2" onclick="closeEditCommentModal()">Close</button>
                    <button type="button" class="btn btn-primary mt-2 mb-2" onclick="editComment()">Post</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Tweet Modal -->
    <div class="modal-overlay" id="editTweetModalOverlay">
        <div class="custom-modal" id="editTweetModal">
            <div class="container mt-2">
                <div>
                    <h1>Edit this tweet</h1>
                    <div class="d-flex justify-content-between">
                        <input type="file" accept=".png, .jpg, .jpeg" class="form-control-file mb-2" name="editTweetImage" id="editTweetImage"></input>
                        <div type="button" style="background-color: rgba(0, 0, 0, 0);" onclick="closeEditTweetModal()">&times;</div>
                    </div>
                    <textarea class="form-control" name="caption" id="editTweet" cols="30" rows="8"></textarea>
                    <button type="button" class="btn btn-danger mt-2 mb-2" onclick="closeEditTweetModal()">Close</button>
                    <button type="button" class="btn btn-primary mt-2 mb-2" onclick="editTweet()">Post</button>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    window.onload = fetchTweets();

    function fetchTweets() {
        fetch(`api/tweet/get.php`)
        .then(res => res.json())
        .then(res => loadTweets(res));

        document.querySelector("#profile-link").href = `profile.php?id=${localStorage.getItem("user_id")}`
    }

    function loadTweets(data) {
        const container = document.querySelector("#tweet-container");
        container.innerHTML = ``;

        data.forEach(tweet => {
            if(tweet === null) { return; }
            let card = ``;

            let profile_picture;
            if(tweet.user.profile_picture) { profile_picture = tweet.user.profile_picture } else { profile_picture = "default.png" }

            let comments_components = ``;

            if(tweet.comments.length > 0) {
                tweet.comments.forEach(comment => {
                    let component = ``;

                    let profile_picture_comment;
                    if(comment.user.profile_picture) { profile_picture_comment = comment.user.profile_picture } else { profile_picture_comment = "default.png" }

                    component = `
                    <div class="border-top ms-5" aria-label="comment-${comment.id}">
                        <div class="d-flex gap-2 mx-4 my-2">
                                <img src="profile_pictures/${profile_picture_comment}" alt="${comment.user.username}" style="max-height: 48px; max-width: 48px; border-radius: 20%">
                            <div class="d-flex justify-content-between align-items-center" style="width: 100%;">
                                <span class="fw-bold">${comment.user.username}</span>
                                <div class="d-flex gap-2">
                                    ${comment.user_id === localStorage.getItem("user_id") ? `<button class="btn btn-warning" onclick="openEditCommentModal(${comment.id})">Edit</button>
                                    <button class="btn btn-danger" onclick="deleteComment(${comment.id})">Delete</button>` : ""}
                                </div>
                            </div>
                        </div>
                        <div class="mx-4 my-2">
                            <p>${comment.comment}</p>
                        </div>
                        ${comment.media ? `<div class="d-flex justify-content-center mb-2">
                            <img src="attachments/${comment.media}" alt="comment-${comment.id}" style="max-width: 90%; max-height: 35vh" class="img-thumbnail">
                        </div>` : ""}
                    </div>
                    `

                    comments_components += component;
                })
            }

            card = `
                    <div class="border-top border-bottom" aria-label="tweet-${tweet.id}">
                        <div class="d-flex gap-2 mx-4 my-2">
                                <img src="profile_pictures/${profile_picture}" alt="${tweet.user.username}" style="max-height: 48px; max-width: 48px; border-radius: 20%">
                            <div class="d-flex justify-content-between align-items-center" style="width: 100%;">
                                <span class="fw-bold">${tweet.user.username}</span>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary" onclick="openCreateCommentModal(${tweet.id})">Comment</button>
                                    ${tweet.user_id === localStorage.getItem("user_id") ? `
                                    <button class="btn btn-warning" onclick="openEditTweetModal(${tweet.id})">Edit</button>
                                    <button class="btn btn-danger" onclick="deleteTweet(${tweet.id})">Delete</button>` : ""}
                                </div>
                            </div>
                        </div>
                        <div class="mx-4 my-2">
                            <p>${tweet.tweet}</p>
                        </div>
                        ${tweet.media ? `<div class="d-flex justify-content-center mb-2">
                            <img src="attachments/${tweet.media}" alt="tweet-${tweet.id}" style="max-width: 90%; max-height: 35vh" class="img-thumbnail">
                        </div>` : ""}
                        ${comments_components}
                    </div>
                    `
            
            container.innerHTML += card;
        });
    }

    function tweet() {
        const tweet = document.querySelector("#tweet").value;
        const image = document.querySelector("#image").files[0];

        const data = new FormData();
        data.append('tweet', tweet);
        data.append('image', image);

        fetch(`api/tweet/create.php`, {
            method: "POST",
            body: data,
        })
        .then(res => res.json())
        .then(res => {
            if(res.status !== 201) {
                alert(res.message);
                return
            }

            fetchTweets();
        })
        .catch(err => {
            alert(err);
        })

        document.querySelector("#tweet").value = "";
        document.querySelector("#image").value = null;
    }

    function openCreateCommentModal(tweetId) {
        sessionStorage.setItem("create_comment_tweet_id", tweetId);

        const createCommentModalOverlay = document.querySelector("#createCommentModalOverlay");
        const createCommentModal = document.querySelector("#createCommentModal");

        createCommentModalOverlay.classList.add('open');
        createCommentModal.classList.add('open');
    }

    function closeCreateCommentModal() {
        sessionStorage.removeItem("create_comment_tweet_id");

        const createCommentModalOverlay = document.querySelector("#createCommentModalOverlay");
        const createCommentModal = document.querySelector("#createCommentModal");

        createCommentModalOverlay.classList.remove('open');
        createCommentModal.classList.remove('open');
    }

    function makeComment() {
        const tweet_id = sessionStorage.getItem("create_comment_tweet_id");

        const comment = document.querySelector("#createComment").value;
        const image = document.querySelector("#createCommentImage").files[0];

        const data = new FormData();
        data.append('tweet_id', tweet_id);
        data.append('comment', comment);
        data.append('image', image);

        fetch(`api/comment/create.php`, {
            method: "POST",
            body: data,
        })
        .then(res => res.json())
        .then(res => {
            if(res.status != 201) {
                alert(res.message);
                return;
            }

            fetchTweets();
        });

        
        document.querySelector("#createComment").value = "";
        document.querySelector("#createCommentImage").value = null;

        closeCreateCommentModal();

    }

    function openEditCommentModal(commentId) {
        sessionStorage.setItem("edit_comment_id", commentId);

        const editCommentModalOverlay = document.querySelector("#editCommentModalOverlay");
        const editCommentModal = document.querySelector("#editCommentModal");

        editCommentModalOverlay.classList.add('open');
        editCommentModal.classList.add('open');
    }

    function closeEditCommentModal() {
        sessionStorage.removeItem("edit_comment_id");

        const editCommentModalOverlay = document.querySelector("#editCommentModalOverlay");
        const editCommentModal = document.querySelector("#editCommentModal");

        editCommentModalOverlay.classList.remove('open');
        editCommentModal.classList.remove('open');
    }

    function editComment() {
        const comment_id = sessionStorage.getItem("edit_comment_id");
        
        const comment = document.querySelector("#editComment").value;
        const image = document.querySelector("#editCommentImage").files[0];

        const data = new FormData();
        data.append('comment_id', comment_id);
        data.append('comment', comment);
        data.append('image', image);

        fetch(`api/comment/edit.php`, {
            method: "POST",
            body: data,
        })
        .then(res => res.json())
        .then(res => {
            if(res.status != 200) {
                alert(res.message);
                return;
            }

            fetchTweets();
        });
        
        document.querySelector("#editComment").value = "";
        document.querySelector("#editCommentImage").value = null;

        closeEditCommentModal();

    }

    function openEditTweetModal(tweetId) {
        sessionStorage.setItem("edit_tweet_id", tweetId);

        const editTweetModalOverlay = document.querySelector("#editTweetModalOverlay");
        const editTweetModal = document.querySelector("#editTweetModal");

        editTweetModalOverlay.classList.add('open');
        editTweetModal.classList.add('open');
    }

    function closeEditTweetModal() {
        const editTweetModalOverlay = document.querySelector("#editTweetModalOverlay");
        const editTweetModal = document.querySelector("#editTweetModal");

        editTweetModalOverlay.classList.remove('open');
        editTweetModal.classList.remove('open');
    }

    function editTweet() {
        const tweet_id = sessionStorage.getItem("edit_tweet_id");
        
        const tweet = document.querySelector("#editTweet").value;
        const image = document.querySelector("#editTweetImage").files[0];

        const data = new FormData();
        data.append('tweet_id', tweet_id);
        data.append('tweet', tweet);
        data.append('image', image);

        fetch(`api/tweet/edit.php`, {
            method: "POST",
            body: data,
        })
        .then(res => res.json())
        .then(res => {
            if(res.status != 200) {
                alert(res.message);
                return;
            }

            fetchTweets();
        });
        
        document.querySelector("#editTweet").value = "";
        document.querySelector("#editTweetImage").value = null;

        closeEditTweetModal();

    }

    function deleteComment(commentId) {
        fetch(`api/comment/delete.php?id=${commentId}`)
        .then(res => res.json())
        .then(res => {
            if(res.status !== 200) {
                alert(res.message);
                return;
            }

            fetchTweets();
        })
        .catch(err => {
            alert(err);
        });
    }

    function deleteTweet(tweetId) {
        fetch(`api/tweet/delete.php?id=${tweetId}`)
        .then(res => res.json())
        .then(res => {
            if(res.status !== 200) {
                alert(res.message);
                return;
            }

            fetchTweets();
        })
        .catch(err => {
            alert(err);
        });
    }

    function search() {
        const search = document.querySelector("#search").value;

        if(!search) { fetchTweets(); return; }

        fetch(`api/tag/search.php?search=${search}`)
        .then(res => res.json())
        .then(res => loadTweets(res));
    }

    function logout() {
        localStorage.removeItem("user_id");
        window.location.replace("logout.php");
    }
</script>
</html>
<?php require "./auth_guard.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="./styles/profile.css">
    <title>Home</title>
</head>
<body>
    <div class="d-flex">
        <nav class="d-flex justify-content-center" style="width: 30vw">
            <div class="d-flex flex-column mt-5 gap-3">
                <a href="home.php">
                    <div class="nav-item">Home</div>
                </a>
                <a id="profile-link" href="profile.php">
                    <div class="nav-item selected">Profile</div>
                </a>
                 <a href="logout.php">
                    <div class="nav-item">Log out</div>
                </a>
            </div>
        </nav>
        <div class="border-start border-end border-2" style="width: 40vw; min-height: 100vh;">
            <div class="d-flex justify-content-center flex-column border-bottom border-2">
                <div id="profile-container" class="d-flex flex-column justify-content-center mx-5 gap-2 mt-2 align-items-">
                    
                </div>
            </div>
            <div id="tweet-container">
                
            </div>
        </div>
        <div style="width: 30vw">SEARCH</div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal-overlay" id="editProfileModalOverlay">
        <div class="custom-modal" id="editProfileModal">
            <div class="container mt-2">
                <div>
                    <h1>Edit your profile</h1>
                    <div class="d-flex justify-content-between">
                        <input type="file" accept=".png, .jpg, .jpeg" class="form-control-file mb-2" name="editProfileImage" id="editProfileImage"></input>
                        <div type="button" style="background-color: rgba(0, 0, 0, 0);" onclick="closeEditProfileModal()">&times;</div>
                    </div>
                    <input type="text" name="username" id="editUsername">
                    <textarea class="form-control" name="caption" id="editBio" cols="30" rows="8" maxlength="255"></textarea>
                    <button type="button" class="btn btn-danger mt-2 mb-2" onclick="closeEditProfileModal()">Close</button>
                    <button type="button" class="btn btn-primary mt-2 mb-2" onclick="editProfile()">Post</button>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    window.onload = fetchProfile();
    
    function fetchProfile() {
        const profile_id = window.location.toString().replace("http://localhost:8080/ukk_twitter/profile.php?id=", "");

        fetch(`api/user/get.php?id=${profile_id}`)
        .then(res => res.json())
        .then(res => loadProfile(res))
     
        document.querySelector("#profile-link").href = `profile.php?id=${profile_id}`;
    }

    function loadProfile(res) {
        let profile_picture = "";

        if(res.profile.profile_picture !== null) { profile_picture = res.profile.profile_picture } else { profile_picture = "default.png" }

        const profile = `
        <div class="d-flex gap-3">
        <div class="d-flex flex-column justify-content-center">
            <img src="profile_pictures/${profile_picture}" alt="${res.profile.username}" style="max-width: 96px; max-height: 96px; border-radius: 20%;">
            <span>${res.profile.username}</span>
        </div>
        <div>
            <p>${res.profile.bio ? res.profile.bio : ""}</p>
        </div>
        </div>
            <div>
            ${res.profile.id === localStorage.getItem("user_id") ? `<button type="button" class="btn btn-primary mb-2" onclick="openEditProfileModal()">Edit</button>` : ""}
        </div>
            
        `;

        document.querySelector("#profile-container").innerHTML = profile;
        const container = document.querySelector("#tweet-container");
        container.innerHTML = ``;

        res.tweets.forEach(tweet => {
            let card = ``;
            card = `
                <div class="border-top border-bottom" aria-label="tweet-${tweet.id}">
                    <div class="d-flex gap-2 mx-4 my-2">
                            <img src="profile_pictures/${profile_picture}" alt="${res.profile.username}" style="max-height: 48px; max-width: 48px; border-radius: 20%">
                        <div class="d-flex justify-content-between align-items-center" style="width: 100%;">
                            <span class="fw-bold">${res.profile.username}</span>
                            ${res.profile.id === localStorage.getItem("user_id") ? `<div class="d-flex gap-2">
                                <button class="btn btn-warning">Edit</button>
                                <button class="btn btn-danger">Delete</button>
                            </div>` : ""}
                        </div>
                    </div>
                    <div class="mx-4 my-2">
                        <p>${tweet.tweet}</p>
                    </div>
                    ${tweet.media ? `<div class="d-flex justify-content-center mb-2">
                        <img src="attachments/${tweet.media}" alt="tweet-${tweet.id}" style="max-width: 90%; max-height: 35vh" class="img-thumbnail">
                    </div>` : ""}
                </div>
            `

            container.innerHTML += card;
        })
    }

    function openEditProfileModal(profileId) {
        sessionStorage.setItem("edit_profile_id", profileId);

        const editProfileModalOverlay = document.querySelector("#editProfileModalOverlay");
        const editProfileModal = document.querySelector("#editProfileModal");

        editProfileModalOverlay.classList.add('open');
        editProfileModal.classList.add('open');
    }

    function closeEditProfileModal() {
        sessionStorage.removeItem("edit_profile_id");

        const editProfileModalOverlay = document.querySelector("#editProfileModalOverlay");
        const editProfileModal = document.querySelector("#editProfileModal");

        editProfileModalOverlay.classList.remove('open');
        editProfileModal.classList.remove('open');
    }

    function editProfile() {
        const profile_id = sessionStorage.getItem("edit_profile_id");
        
        const username = document.querySelector("#editUsername").value;
        const bio = document.querySelector("#editBio").value;
        const image = document.querySelector("#editProfileImage").files[0];

        const data = new FormData();
        data.append('username', username);
        data.append('bio', bio);
        data.append('image', image);

        fetch(`api/user/edit.php`, {
            method: "POST",
            body: data,
        })
        .then(res => res.json())
        .then(res => {
            if(res.status != 200) {
                alert(res.message);
                return;
            }

            fetchProfile();
        });
        
        document.querySelector("#editUsername").value = "";
        document.querySelector("#editBio").value = "";
        document.querySelector("#editProfileImage").value = null;

        closeEditProfileModal();
    }
</script>
</html>
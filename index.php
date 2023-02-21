<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="./styles/global.css">
    <title>Log in</title>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center" style="min-height: 100vh">
        <form action="" class="d-flex flex-column gap-3" style="width: 25vw;">
            <div class="form-floating">
                <input type="email" name="email" id="email" placeholder="Email" class="form-control" required>
                <label for="email">Email</label>
            </div>
            <div class="form-floating">
                <input type="password" name="password" id="password" placeholder="Password" class="form-control" required>
                <label for="password">Password</label>
            </div>
            <button type="button" class="btn btn-primary" onclick="login()">Log in</button>
            <span>Don't have an account? <a href="register.php">Sign up</a></span>
        </form>
    </div>
</body>
<script>
    function login() {
        const email = document.querySelector("#email").value;
        const password = document.querySelector("#password").value;

        const body = {email, password};

        fetch(`api/auth/login.php`, {
            method: "POST",
            body: JSON.stringify(body)
        })
        .then(res => res.json())
        .then(res => {
            if(res.status != 200) {
                alert(res.message);
                return;
            }

            localStorage.setItem("user_id", res.user_id);
            window.location.replace("home.php");
        })
    }
</script>
</html>
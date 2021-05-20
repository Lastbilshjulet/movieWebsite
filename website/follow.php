<?php
    include_once 'connection.php';
?>

<!doctype html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>FF movies</title>

    <link rel="shortcut icon" type="image/ico" href="media/favicon.ico"/>
    <link rel="stylesheet" href="stylesheet.css">

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css">
</head>

<body class="d-flex flex-column h-100">

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Eighth navbar example">
            <div class="container">
                <a class="navbar-brand" href="index.php">FF movies</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarsExample07" aria-controls="navbarsExample07" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarsExample07">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="movies.php">Movie list</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="whowatched.php">Who watched</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="latest.php">Latest watched</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="stats.php">Stats</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="follow.php">Follow</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Begin page content -->
    <main class="container p-0">
        <div id="stats">
            <h2 id="title" class="m-4">Start following people to see what movies they have watched</h2>
            <div id="followPrompt" class="m-4">
                <form class="col-sm-4 col-12" role="form" method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                        <?php
                            $sql = "SELECT userName
                            FROM user
                            WHERE userID NOT IN (SELECT followlist.following 
                                            FROM followlist 
                                            INNER JOIN user 
                                            ON user.userID = followlist.user
                                            WHERE user.userName = \"".$_COOKIE["usrnm"]."\")
                            ORDER BY userName";
                            $sql_ = mysqli_query($conn, $sql);
                            if (mysqli_num_rows($sql_) == 0)
                            {
                                echo "<h4>You already follow everyone</h4>";
                            }
                            else
                            {
                                echo "<select class='form-select mb-3' name='user'>";
                                while ($row = mysqli_fetch_assoc($sql_))
                                {
                                    echo "<option value='".$row["userName"]."'>".$row["userName"]."</option>";
                                }
                                echo "</select>";
                                echo "<input id='submitButton' type='submit' value='Follow' name='submit' class='btn btn-primary mb-3'/>";
                            }
                        ?>
                </form>
                <?php
                    if (isset($_POST["submit"]))
                    {
                        $follow = $_POST["user"];
                        $sql_user = "SELECT userID FROM user WHERE userName = \"".$_COOKIE["usrnm"]."\"";
                        $sql_userID = mysqli_query($conn, $sql_user);
                        while ($row_userID = mysqli_fetch_assoc($sql_userID))
                        {
                            $user = $row_userID["userID"];
                            $sql_follow = "SELECT userID FROM user WHERE userName = \"".$follow."\"";
                            $sql_followID = mysqli_query($conn, $sql_follow);
                            while ($row_followID = mysqli_fetch_assoc($sql_followID))
                            {
                                $sql = "INSERT INTO followlist VALUES (\"".$user."\", \"".$row_followID["userID"]."\")";
                                if (!mysqli_query($conn, $sql))
                                {
                                    echo "<div class=\"alert alert-danger col-sm-4 col-12\" role=\"alert\"><b>Error:</b> ".$sql." ".mysqli_error($conn).".</div>";
                                }
                                else
                                {
                                    echo "<div class=\"alert alert-success col-sm-4 col-12\" role=\"alert\">You now follow <b>".$follow."</b>.</div>";
                                }
                            }
                        }
                    }
                ?>
            </div>
            <div>
                <?php
                    $sql = "SELECT userID, userName
                    FROM user
                    WHERE userID IN (SELECT followlist.following 
                                    FROM followlist 
                                    INNER JOIN user 
                                    ON user.userID = followlist.user
                                    WHERE user.userName = \"".$_COOKIE["usrnm"]."\")
                    ORDER BY userName";
                    $sql_ = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($sql_) > 0)
                    {
                        echo "<h4 class='m-4'>You follow:</h4>";
                        while ($row = mysqli_fetch_assoc($sql_))
                        {
                            if ($row["userName"] != $_COOKIE["usrnm"])
                            {
                                echo "<h6 class='mx-5'><a href='whowatched.php?user=".$row["userID"]."&submitWho=See+movies' class='link-dark'>".$row["userName"]."</a></h6>";
                            }
                        }
                    }
                ?>
            </div>
        </div>
    </main>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <h6 class="text-center text-muted pt-2"><i class="far fa-copyright"></i> 2021</h6>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf"
        crossorigin="anonymous"></script>

</body>

</html>

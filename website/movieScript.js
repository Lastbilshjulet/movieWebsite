'use strict';

window.addEventListener("load", () => {
    for (let i = 1; i <= 250; i++)
    {
        document.getElementById("movie" + i).addEventListener("click", () => {
            let movieInfo = document.getElementById("movieInfo" + i);
            if (movieInfo.style.display === "none") {
                document.getElementById("movie" + i).classList.remove("text-truncate");
                movieInfo.style.display = "";
            } else {
                document.getElementById("movie" + i).classList.add("text-truncate");
                movieInfo.style.display = "none";
            }
        });
    }

    var cValue = document.cookie;
    if (cValue.length > 0) {
        let cArray = cValue.split('=');
        document.getElementById("movieList").classList.remove("d-none");
        document.getElementById("title").textContent = "Welcome " + cArray[1] + "!";
    }
    else {
        document.getElementById("usernamePrompt").classList.remove("d-none");
    }
    
    document.getElementById("floatingInput").addEventListener("keyup", (e) => {
        e.preventDefault();
        if (e.key === "Enter") {
            saveAndShow();
        }
    });

    document.getElementById("submitButton").addEventListener("click", () => {
        if (document.getElementById("floatingInput").value != "" && document.getElementById("floatingInput").value.length <= 30) {
            saveAndShow();
        }
    });

    document.getElementById("hideseenmovies").addEventListener("click", () => {
        let x = document.getElementsByClassName("hideifseen");
        if (x[0].classList.contains("d-none"))
        {
            document.getElementById("hideseenmovies").textContent = "Hide seen movies";
            for (let i = 0; i < x.length; i++)
            {
                x[i].classList.remove("d-none");
            }
        }
        else
        {
            document.getElementById("hideseenmovies").textContent = "Show seen movies";
            for (let i = 0; i < x.length; i++)
            {
                x[i].classList.add("d-none");
            }
        }
    });
});

function saveAndShow() {
    let name = saveCookie();
    showlist();
    document.getElementById("title").textContent = "Welcome " + name + "!";
}

function showlist() {
    document.getElementById("usernamePrompt").classList.add("d-none");
    document.getElementById("movieList").classList.remove("d-none");
}

function saveCookie() {
    let name = document.getElementById("floatingInput").value;
    if (name.charAt(0) != name.charAt(0).toUpperCase()) {
        name = name.substring(0, 0) + name.charAt(0).toUpperCase() + name.substring(1);
    }
    document.cookie = "usrnm=" + name + "; expires=Tue, 31 Dec 2030 12:00:00 UTC";
    console.log("saved cookie for " + name);
    return name;
}

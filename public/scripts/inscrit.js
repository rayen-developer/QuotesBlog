

function inscrit() {
    var pass = document.getElementById("password").value;
    var pass1 = document.getElementById("password1").value;
    if (pass == pass1) {
        document.getElementById('submit').type = 'submit';
        document.getElementById('submit').click;



    }
    else {
        alert("Les mots de passe que vous avez entrés ne sont pas identiques.");

        document.getElementById('reset').click();


    }

}

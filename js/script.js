// ===============================
// script.js
// ===============================

// Phantom captcha blocker
(function() {
    const oldFetch = window.fetch;
    window.fetch = function(input, init) {
        if (typeof input === 'string' && input.includes('captcha.php')) {
            console.warn('Blocked phantom captcha.php request');
            return Promise.resolve(new Response(JSON.stringify({}), {status:200}));
        }
        return oldFetch.apply(this, arguments);
    };

    const oldXHROpen = XMLHttpRequest.prototype.open;
    XMLHttpRequest.prototype.open = function(method, url) {
        if (url.includes('captcha.php')) {
            console.warn('Blocked phantom captcha.php request');
            this.abort();
        } else {
            oldXHROpen.apply(this, arguments);
        }
    };
})();


// ===============================
// AJAX form submission with field-specific errors
// ===============================

const registerForm = document.getElementById("registerForm");

if (registerForm) {
    registerForm.addEventListener("submit", function(e){
        e.preventDefault();

        let form = this;
        let formData = new FormData(form);
        let responseBox = document.getElementById("form-response");
        let button = form.querySelector("button");

        // Reset errors
        form.querySelectorAll(".error").forEach(el => el.textContent = "");
        form.querySelectorAll(".textbox").forEach(el => el.classList.remove("input-error"));

        responseBox.innerHTML = "Submitting...";
        button.disabled = true;

        fetch(form.action, {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {

            if (data.status === "success") {
                responseBox.innerHTML = `<span style="color:green;">${data.message}</span>`;
                form.reset();
                button.disabled = false;

                // Optional redirect
                setTimeout(() => {
                    window.location.href = "signin.html";
                }, 2000);

            } else if (data.status === "error") {

                if (data.field) {
                    let field = form.querySelector(`[name="${data.field}"]`);
                    let errorSpan = field.nextElementSibling;

                    if (field) {
                        field.classList.add("input-error");
                    }

                    if (errorSpan) {
                        errorSpan.textContent = data.message;
                    }

                    responseBox.innerHTML = "";
                } else {
                    responseBox.innerHTML = `<span style="color:red;">${data.message}</span>`;
                }

                button.disabled = false;
            }

        })
        .catch(() => {
            responseBox.innerHTML = `<span style="color:red;">Submission failed. Try again.</span>`;
            button.disabled = false;
        });
    });
}

const passwordInput = document.getElementById("password");
const strengthText = document.getElementById("password-strength");

if (passwordInput) {
    passwordInput.addEventListener("input", function() {
        let val = this.value;

        if (val.length < 6) {
            strengthText.textContent = "Weak password";
            strengthText.style.color = "red";
        } else if (val.match(/[A-Z]/) && val.match(/[0-9]/)) {
            strengthText.textContent = "Strong password";
            strengthText.style.color = "green";
        } else {
            strengthText.textContent = "Medium password";
            strengthText.style.color = "orange";
        }
    });
}

function togglePassword() {
    const password = document.getElementById("password");
    password.type = password.type === "password" ? "text" : "password";
}

// Contact Form (clean version)
const contactForm = document.getElementById("contact-form");

if (contactForm) {
    contactForm.addEventListener("submit", function(e){
        e.preventDefault();

        let form = this;
        let formData = new FormData(form);
        let messageBox = document.getElementById("message-outcome");

        messageBox.style.display = "block";
        messageBox.innerHTML = "Sending message...";

        fetch(form.action, {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(data => {

            // If your PHP returns plain text like "success"
            if (data.trim() === "success") {
                messageBox.innerHTML = "<span style='color:green;'>Message sent successfully!</span>";
                form.reset();
            } else {
                messageBox.innerHTML = "<span style='color:red;'>" + data + "</span>";
            }

        })
        .catch(() => {
            messageBox.innerHTML = "<span style='color:red;'>Error sending message.</span>";
        });
    });
}



fetch("https://ipapi.co/json/")
.then(res => res.json())
.then(data => {

    const country = data.country;

    if(country === "NG"){
        highlightGroup("nigeria");
    }
    else if(country === "US"){
        highlightGroup("usa");
    }
    else if(country === "GB"){
        highlightGroup("uk");
    }

});

function highlightGroup(type){
    const cards = document.querySelectorAll(".group-card");

    cards.forEach(card => {
        if(card.innerText.toLowerCase().includes(type)){
            card.style.border = "3px solid #25D366";
            card.style.transform = "scale(1.05)";
        }
    });
}

function expandPDF(el) {
    el.classList.toggle('expanded');
}


window.addEventListener('scroll', function () {
    const element = this.classList.toggle('expanded');
    const position = element.getBoundingClientRect().top;
    const screenHeight = window.innerHeight;

    if (position < screenHeight - 100) {
        element.classList.add('show');
    }
});

function openFull(e) {
    e.stopPropagation();
    window.open(
        "https://drive.google.com/file/d/1NhmD7RtWtA9x5V6lqTwuFTRIyE83NxeI/preview",
        "_blank"
    );
}

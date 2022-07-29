// A reference to Stripe.js initialized with your real test publishable API key.
var stripe = Stripe(
    "pk_test_51I6FiPI1bUvA25HFFDKUbDYyjLwiAgN2JWruCQ7C6NGlTYE5Q2B8ICJ321lQ9sBp7AbGbpGQ17UWa0TxFBuqp01T00t93E8Rvc"
);
// The items the customer wants to buy

var elements = stripe.elements();

var dataForm = {
    firstname: false,
    lastname: false,
    email: false,
    card: false,
};

// Set up Stripe.js and Elements to use in checkout form
var style = {
    base: {
        color: "#32325d",
        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
        fontSmoothing: "antialiased",
        fontSize: "16px",
        "::placeholder": {
            color: "#aab7c4",
        },
    },
    invalid: {
        color: "#fa755a",
        iconColor: "#fa755a",
    },
};

var cardElement = elements.create("card", {
    style: style,
    hidePostalCode: true,
});
cardElement.mount("#card-element");

cardElement.on("change", function (event) {
    if (event.complete) {
        // document.querySelector('#submit').disabled = false;
        dataForm.card = true;
    } else if (event.error) {
        dataForm.card = false;
        // show validation to customer
    }

    formValid(dataForm);
});

var form = document.getElementById("payment-form");

form.addEventListener("submit", function (event) {
    // We don't want to let default form submission happen here,
    // which would refresh the page.
    event.preventDefault();
    formValid(dataForm);

    if (!document.getElementById("submit").disabled) {
        loading(true);
        const data = formData();

        stripe
            .createPaymentMethod({
                type: "card",
                card: cardElement,
                billing_details: {
                    // Include any additional collected billing details.
                    name: data["firstname"] + " " + data["lastname"],
                    email: data["email"],
                },
                metadata: {
                    cookieId: data["cookieId"],
                },
            })
            .then(stripePaymentMethodHandler);
    }
});

function stripePaymentMethodHandler(result) {
    if (result.error) {
        showError(result.error);
    } else {
        // Otherwise send paymentMethod.id to your server (see Step 4)
        fetch("/panier/checkout-session", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify({
                payment_method_id: result.paymentMethod.id,
            }),
        }).then(function (result) {
            // Handle server response (see Step 4)
            result.json().then(function (json) {
                handleServerResponse(json);
            });
        });
    }
}

function handleServerResponse(response) {
    if (response.error) {
        showError(response.error);
    } else if (response.requires_action) {
        // Use Stripe.js to handle required card action
        stripe
            .handleCardAction(response.payment_intent_client_secret)
            .then(handleStripeJsResult);
    } else {
        // Show success message
        orderComplete(response.id);
    }
}

function handleStripeJsResult(result) {
    if (result.error) {
        showError(result.error.message);
    } else {
        // The card action has been handled
        // The PaymentIntent can be confirmed again on the server
        fetch("/panier/checkout-session", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
            },
            body: JSON.stringify({
                payment_intent_id: result.paymentIntent.id,
            }),
        })
            .then(function (confirmResult) {
                return confirmResult.json();
            })
            .then(handleServerResponse);
    }
}

/* ------- UI helpers ------- */
// Shows a success message when the payment is complete
var orderComplete = function (paymentIntentId) {
    loading(false);
    window.location.replace("/panier/order-complete");
    document
        .querySelector(".result-message a")
        .setAttribute(
            "href",
            "https://dashboard.stripe.com/test/payments/" + paymentIntentId
        );
    document.querySelector(".result-message").classList.remove("hidden");
    document.querySelector("button").disabled = true;
};
// Show the customer the error from Stripe if their card fails to charge
var showError = function (errorMsgText) {
    loading(false);
    var errorMsg = document.querySelector("#card-error");
    errorMsg.textContent = errorMsgText;
    setTimeout(function () {
        errorMsg.textContent = "";
    }, 20000);
};

// Show a spinner on payment submission
var loading = function (isLoading) {
    if (isLoading) {
        // Disable the button and show a spinner
        document.querySelector("button").disabled = true;
        document.querySelector("#spinner").classList.remove("hidden");
        document.querySelector("#button-text").classList.add("hidden");
    } else {
        document.querySelector("button").disabled = false;
        document.querySelector("#spinner").classList.add("hidden");
        document.querySelector("#button-text").classList.remove("hidden");
    }
};

var formData = function () {
    var result = {};

    document.querySelectorAll("#payment-form input[id]").forEach((element) => {
        result[element.id] = element.value;
    });

    return result;
};

var regexName = /^[A-Za-zÀ-ÿà-ÿ]*[-A-Za-zÀ-ÿà-ÿ]*[A-Za-zÀ-ÿà-ÿ]+$/;
var regexEmail =
    /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;

document.getElementById("firstname").addEventListener("change", function (e) {
    const errorElem = document.getElementById("firstnameError");
    var valid = true;

    if (this.value.length < 2) {
        valid = false;
        errorElem.innerHTML = "Vous devez indiquer votre prénom";
    } else if (regexName.test(this.value) == false) {
        valid = false;
        errorElem.innerHTML = "Vous devez indiquer un prénom conforme";
    } else {
        errorElem.innerHTML = "";
    }

    !valid
        ? errorElem.classList.add("show")
        : errorElem.classList.remove("show");

    dataForm.firstname = valid;
    formValid(dataForm);
});

document.getElementById("lastname").addEventListener("change", function (e) {
    const errorElem = document.getElementById("lastnameError");
    var valid = true;

    if (this.value.length < 2) {
        valid = false;
        errorElem.innerHTML = "Vous devez indiquer votre nom de famille";
    } else if (regexName.test(this.value) == false) {
        valid = false;
        errorElem.innerHTML = "Vous devez indiquer un nom de famille correct";
    } else {
        errorElem.innerHTML = "";
    }

    !valid
        ? errorElem.classList.add("show")
        : errorElem.classList.remove("show");

    dataForm.lastname = valid;
    formValid(dataForm);
});

document.getElementById("email").addEventListener("change", function (e) {
    const errorElem = document.getElementById("emailError");
    var valid = true;

    if (!regexEmail.test(this.value)) {
        valid = false;
        errorElem.innerHTML = "Vous devez indiquer un email correct";
    } else {
        valid = true;
        errorElem.innerHTML = "";
    }

    !valid
        ? errorElem.classList.add("show")
        : errorElem.classList.remove("show");

    dataForm.email = valid;
    formValid(dataForm);
});

function formValid($datas) {
    var result = true;

    for (const property in $datas) {
        if ($datas[property] == false) {
            result = false;
        }
    }

    if (result) {
        document.querySelector("#submit").disabled = false;
    } else {
        document.querySelector("#submit").disabled = true;
    }
}

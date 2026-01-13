// Open Ratings Modal
function openRatingsModal(button) {
  const nurseId = button.getAttribute("data-nurse-id");
  document.getElementById("modal-nurse-name").textContent = ""; // Clear previous name
  document.getElementById("ratings-list").innerHTML = ""; // Clear previous ratings

  // Fetch ratings for the selected nurse
  fetch(`get_nurse_ratings.php?nurse_id=${nurseId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update the modal with nurse name and ratings
        document.getElementById("modal-nurse-name").textContent =
          data.nurse_name;

        let ratingsHtml = "";
        if (data.ratings.length > 0) {
          data.ratings.forEach((rating) => {
            ratingsHtml += `
                            <div class="rating-item">
                                <span><strong>Rating:</strong> ${
                                  rating.rating
                                }/5</span><br>
                                <span><strong>Comment:</strong> ${
                                  rating.comment || "No comment"
                                }</span>
                            </div>
                        `;
          });
        } else {
          ratingsHtml = "<p>No ratings available for this nurse.</p>";
        }

        document.getElementById("ratings-list").innerHTML = ratingsHtml;
        document.getElementById("ratings-modal").style.display = "block";
      } else {
        alert("Failed to load ratings.");
      }
    })
    .catch((error) => console.error("Error fetching ratings:", error));
}

// Close Ratings Modal
function closeRatingsModal() {
  document.getElementById("ratings-modal").style.display = "none";
}
let currentBookingId;

function openReviewModal(button) {
  const bookingId = button.getAttribute("data-booking-id");
  currentBookingId = bookingId;

  // Fetch booking details via AJAX or pre-populate using PHP
  fetch(`get_booking_details.php?booking_id=${bookingId}`)
    .then((response) => response.json())
    .then((data) => {
      document.getElementById("nurse-id").value = data.nurse_id;
      document.getElementById("service-type").value = data.service_type;
      document.getElementById("booking-id").value = data.booking_id;
      document.getElementById("review-modal").style.display = "block";
    })
    .catch((error) => console.error("Error fetching booking details:", error));
}

function closeReviewModal() {
  document.getElementById("review-modal").style.display = "none";
}

// Handle form submission
document
  .getElementById("review-form")
  .addEventListener("submit", function (event) {
    event.preventDefault();
    const formData = new FormData(this);

    fetch("save_review.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Review submitted successfully!");
          closeReviewModal();
          location.reload(); // Refresh page to reflect changes
        } else {
          alert("Failed to submit review. Please try again.");
        }
      })
      .catch((error) => console.error("Error submitting review:", error));
  });


  function payNow(id, amount) {
    // alert(id);
    // alert(amount);

    var address = document.getElementById("address").value;
    var mobile = document.getElementById("phone_number").value;
    var nic = document.getElementById("nic").value;

    var req = new XMLHttpRequest();
    req.onreadystatechange = () => {
        if (req.readyState == 4 && req.status == 200) {
            var obj = JSON.parse(req.responseText);
            // Payment completed. It can be a successful failure.
            payhere.onCompleted = function onCompleted(orderId) {
                console.log("Payment completed. OrderID:" + orderId);
                
                // Send AJAX request to update the database
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "updateRequest.php", true);
                xhr.setRequestHeader("Content-Type", "application/json");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        console.log("Request updated successfully:", xhr.responseText);
                        alert("Booking confirmed successfully!");
                        window.location = "customer_dashboard.php";
                    }
                };
                xhr.send(JSON.stringify({
                    request_id: id,
                    address:address,
                    mobile:mobile,
                    nic:nic,
                    order_id: orderId,
                    total_amount: obj.amount,
                    nursing_charge: obj.nursing_charge,
                    service_charge: obj.service_charge,
                    total_hours: obj.total_hours
                }));
            };
            
            // Payment window closed
            payhere.onDismissed = function onDismissed() {
                console.log("Payment dismissed");
            };
            
            // Error occurred
            payhere.onError = function onError(error) {
                console.log("Error:" + error);
            };
            
            // Put the payment variables here
            var payment = {
                sandbox: true,
                merchant_id: obj.merchant_id,
                return_url: "http://localhost/nurse_allocation_system/confirm_booking.php?request_id=" + id,
                cancel_url: "http://localhost/nurse_allocation_system/confirm_booking.php?request_id=" + id,
                notify_url: "http://sample.com/notify",
                order_id: obj.order_id,
                items: "Nurse care charge",
                amount: obj.amount,
                currency: obj.currency,
                hash: obj.hash,
                first_name: "Saman",
                last_name: "Perera",
                email: "samanp@gmail.com",
                phone: "0771234567",
                address: "No.1, Galle Road",
                city: "Colombo",
                country: "Sri Lanka",
                delivery_address: "No. 46, Galle road, Kalutara South",
                delivery_city: "Kalutara",
                delivery_country: "Sri Lanka",
                custom_1: "",
                custom_2: ""
            };
            
            // Show the payhere.js popup
            payhere.startPayment(payment);
        }
    };
    req.open("GET", "payNowProcess.php?id=" + id + "&amount=" + amount, true);
    req.send();
}
  

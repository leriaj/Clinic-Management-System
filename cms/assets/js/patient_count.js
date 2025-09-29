document.addEventListener("DOMContentLoaded", function () {
    fetch("../assets/php/get_counts.php")
        .then((response) => response.json())
        .then((data) => {
            if (data.patient_count !== undefined) {
                document.getElementById("patients-count").textContent = data.patient_count + "+";
            }
            if (data.doctor_count !== undefined) {
                document.getElementById("doctors-count").textContent = data.doctor_count + "+";
            }
            if (data.nurse_count !== undefined) {
                document.getElementById("nurses-count").textContent = data.nurse_count + "+";
            }
        })
        .catch((error) => console.error("Error fetching counts:", error));
});
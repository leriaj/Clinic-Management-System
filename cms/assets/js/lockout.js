
        const lockoutOverlay = document.getElementById('lockoutOverlay');
        const remainingTimeElement = document.getElementById('remainingTime');

        if (lockoutOverlay && remainingTimeElement) {
            lockoutOverlay.style.display = 'flex';
            let remainingTime = parseInt(remainingTimeElement.textContent);

            const countdown = setInterval(() => {
                remainingTime--;
                remainingTimeElement.textContent = remainingTime;

                if (remainingTime <= 0) {
                    clearInterval(countdown);
                    window.location.href = 'login.php';
                }
            }, 1000);
        }

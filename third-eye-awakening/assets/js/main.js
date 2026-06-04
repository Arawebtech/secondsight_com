      const TIMER_KEY = "third_eye_flexi_timer";
      const COUNTDOWN_DURATION = 30 * 60 * 1000;

      function getTimerDeadline() {
        const stored = localStorage.getItem(TIMER_KEY);
        if (stored && !isNaN(Date.parse(stored))) {
          const parsed = new Date(stored);
          const now = new Date();

          if (parsed.getTime() < now.getTime()) {
            const newDeadline = new Date(now.getTime() + COUNTDOWN_DURATION);
            localStorage.setItem(TIMER_KEY, newDeadline.toISOString());
            return newDeadline;
          }

          return parsed;
        } else {
          const newDeadline = new Date(Date.now() + COUNTDOWN_DURATION);
          localStorage.setItem(TIMER_KEY, newDeadline.toISOString());
          return newDeadline;
        }
      }

      const deadline = getTimerDeadline();

      function updateTimerDisplay() {
        const now = new Date();
        const timeDiff = deadline - now;

        if (timeDiff <= 0) {
          document
            .querySelectorAll(".days")
            .forEach((el) => (el.textContent = "00"));
          document
            .querySelectorAll(".hours")
            .forEach((el) => (el.textContent = "00"));
          document
            .querySelectorAll(".minutes")
            .forEach((el) => (el.textContent = "00"));
          document
            .querySelectorAll(".seconds")
            .forEach((el) => (el.textContent = "00"));
          document
            .getElementById("timer-expired")
            ?.style?.setProperty("display", "block");

          const newDeadline = new Date(Date.now() + COUNTDOWN_DURATION);
          localStorage.setItem(TIMER_KEY, newDeadline.toISOString());

          return;
        }

        const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
        const hours = Math.floor(
          (timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
        );
        const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);

        document
          .querySelectorAll(".days")
          .forEach((el) => (el.textContent = String(days).padStart(2, "0")));
        document
          .querySelectorAll(".hours")
          .forEach((el) => (el.textContent = String(hours).padStart(2, "0")));
        document
          .querySelectorAll(".minutes")
          .forEach((el) => (el.textContent = String(minutes).padStart(2, "0")));
        document
          .querySelectorAll(".seconds")
          .forEach((el) => (el.textContent = String(seconds).padStart(2, "0")));
      }

      updateTimerDisplay();
      var timerInterval = setInterval(updateTimerDisplay, 1000);
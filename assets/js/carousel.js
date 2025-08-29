// Auto-sliding carousel for Our Story section
// Seamless forward-only loop: clone first slide, advance to clone, then jump back to start without visual reverse
document.addEventListener("DOMContentLoaded", function () {
  const track = document.querySelector(".carousel-track");
  if (!track) return;

  const slides = Array.from(track.children);
  if (slides.length < 2) return; // nothing to do

  // Ensure the track has a transition defined (will be used for slides)
  const transitionStyle = "transform 1s ease-in-out";
  track.style.transition = track.style.transition || transitionStyle;

  // Clone first slide and append to create seamless loop
  const firstClone = slides[0].cloneNode(true);
  track.appendChild(firstClone);

  let currentIndex = 0; // index of the visible slide (0-based)
  const totalSlides = slides.length + 1; // includes the clone
  const percentPerSlide = 100 / totalSlides; // distribute track width across items

  // Set widths so translate math works reliably if CSS isn't exact
  // Each child should take equal width; if CSS already sets it, this won't hurt.
  Array.from(track.children).forEach((child) => {
    child.style.width = `${percentPerSlide}%`;
    child.style.flexShrink = "0";
  });

  // Make track a flex row and set its width
  track.style.display = "flex";
  track.style.width = `${totalSlides * 100}%`;

  function goToIndex(index, withTransition = true) {
    if (!withTransition) track.style.transition = "none";
    else track.style.transition = transitionStyle;

    const translateX = -index * percentPerSlide;
    track.style.transform = `translateX(${translateX}%)`;
  }

  function slideNext() {
    currentIndex += 1;
    goToIndex(currentIndex, true);
  }

  // When we reach the cloned slide (end), jump back to the real first slide without transition
  track.addEventListener("transitionend", function () {
    // If we've moved to the cloned first slide, reset to index 0 instantly
    if (currentIndex === totalSlides - 1) {
      // disable transition, snap back to start
      currentIndex = 0;
      // allow the browser a tick to apply transition:none
      requestAnimationFrame(() => {
        goToIndex(currentIndex, false);
        // Force reflow then restore transition for next slide
        void track.offsetWidth;
        track.style.transition = transitionStyle;
      });
    }
  });

  // Start autoplay
  const intervalMs = 3000;
  let intervalId = setInterval(slideNext, intervalMs);

  // Pause on hover (optional UX improvement)
  track.addEventListener("mouseenter", () => clearInterval(intervalId));
  track.addEventListener("mouseleave", () => {
    clearInterval(intervalId);
    intervalId = setInterval(slideNext, intervalMs);
  });

  // initialise
  goToIndex(0, false);
});

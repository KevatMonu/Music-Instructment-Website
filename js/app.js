

function down() {
  let support = document.querySelector("#Support");
  let box = document.querySelector(".support-item");
  let rot = document.querySelector("#arr");

  support.addEventListener("mouseenter", () => {
    console.log("Enter");
    gsap.to(box, {
      opacity: 1,
      pointerEvents: "auto",
      visibility: "visible",
      duration: 0.4,
      ease: "power1.out",
    });

    gsap.to(rot, {
      rotate: 180,
      duration: 0.3,
      ease: "power2.out",
    });
  });

  support.addEventListener("mouseleave", () => {
    console.log("Enter");
    gsap.to(box, {
      opacity: 0,
      pointerEvents: "none",
      visibility: "hidden",
      duration: 0.4,
      ease: "power1.out",
    });

    gsap.to(rot, {
      rotate: 0,
      duration: 0.3,
      ease: "power2.in",
    });
  });
}

function downlast() {
  let support = document.querySelector("#About");
  let box = document.querySelector(".about-item");
  let rot = document.querySelector("#ar");

  support.addEventListener("mouseenter", () => {
    gsap.to(box, {
      opacity: 1,
      pointerEvents: "auto",
      visibility: "visible",
      duration: 0.4,
      ease: "power1.out",
    });

    gsap.to(support, {
      duration: 0.1,
      ease: "power1.out",
    });

    gsap.to(rot, {
      rotate: 180,
      duration: 0.3,
      ease: "power2.out",
    });
  });

  support.addEventListener("mouseleave", () => {
    console.log("Enter");
    gsap.to(box, {
      opacity: 0,
      pointerEvents: "none",
      visibility: "hidden",
      duration: 0.4,
      ease: "power1.out",
    });

    gsap.to(rot, {
      rotate: 0,
      duration: 0.3,
      ease: "power2.in",
    });

    gsap.to(support, {
      duration: 0.4,
      ease: "power1.out",
    });
  });
}

function uptext() {
  let products = document.querySelectorAll(".product-item"); // Select all product items

  products.forEach((product) => {
    let text = product.querySelector(".shop-btn"); // Select the corresponding .shop-btn for each product

    product.addEventListener("mouseenter", () => {
      gsap.to(text, {
        y: -10,
        duration: 0.3,
        ease: "power1.out",
        opacity: 1,
      });
    });

    product.addEventListener("mouseleave", () => {
      gsap.to(text, {
        y: 0,
        duration: 0.3,
        ease: "power1.out",
        opacity: 0,
      });
    });
  });
}

function productswiper() {
  const swiperWrapper = document.querySelector(".product-list");
  const prevBtn = document.querySelector(".prev-btn");
  const nextBtn = document.querySelector(".next-btn");

  let currentPosition = 0; // Track current position
  const itemWidth = document.querySelector(".product-item").offsetWidth + 16; // Include margin
  const totalItems = document.querySelectorAll(".product-item").length;
  const maxPosition = (totalItems - Math.floor(90 / 20)) * itemWidth; // Remaining scroll width

  // Next button click event
  nextBtn.addEventListener("click", () => {
    if (currentPosition > -maxPosition) {
      currentPosition -= itemWidth;
      swiperWrapper.style.transform = `translateX(${currentPosition}px)`;
    }
  });

  // Previous button click event
  prevBtn.addEventListener("click", () => {
    if (currentPosition < 0) {
      currentPosition += itemWidth;
      swiperWrapper.style.transform = `translateX(${currentPosition}px)`;
    }
  });
}

function selliconup() {
  let boxes = document.querySelectorAll(".sell-item");

  boxes.forEach((item) => {
    let icon = item.querySelector(".sell-icon");
    item.addEventListener("mouseenter", () => {
      gsap.to(icon, {
        y: -10,
        duration: 0.3,
        ease: "power1.out",
        opacity: 1,
      });
    });

    item.addEventListener("mouseleave", () => {
      gsap.to(icon, {
        y: 0,
        duration: 0.3,
        ease: "power1.out",
        opacity: 0,
      });
    });
  });
}

// Target date (YYYY-MM-DD HH:MM:SS format)

function updateCountdown() {
  const targetDate = new Date("2025-12-31T23:59:59");
  const now = new Date();
  const diffTime = targetDate - now;

  if (diffTime > 0) {
    // Calculate time components
    const days = Math.floor(diffTime / (1000 * 60 * 60 * 24));
    const hours = Math.floor(
      (diffTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
    );
    const minutes = Math.floor((diffTime % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diffTime % (1000 * 60)) / 1000);

    // Update the DOM elements
    document.getElementById("days").textContent = days
      .toString()
      .padStart(2, "0");
    document.getElementById("hours").textContent = hours
      .toString()
      .padStart(2, "0");
    document.getElementById("minutes").textContent = minutes
      .toString()
      .padStart(2, "0");
    document.getElementById("seconds").textContent = seconds
      .toString()
      .padStart(2, "0");
  } else {
    // When the target date has passed
    document.querySelector(".counter").innerHTML =
      "<p>The target date has passed!</p>";
    clearInterval(timerInterval); // Stop the interval
  }
  const timerInterval = setInterval(updateCountdown, 1000);
}

function productroll(){
  var carousel = new Swiper(".myCarousel", {
    slidesPerView: 4,
    spaceBetween: 25,
    pagination: {
      el: ".carousel-pagination",
      clickable: true,
    },
    speed: 600, 
    slidesPerGroup: 4,
  });
  
}

function categoryup() {
  const categories = [
    { button: "#best-seller", container: "#best-seller-container" },
    { button: "#top-rating", container: "#top-rating-container" },
    { button: "#hot-trends", container: "#hot-trends-container" },
    { button: "#featured-product", container: "#featured-product-container" },
  ];

    

  categories.forEach(({ button, container }, index) => {
    const buttonElement = document.querySelector(button);
    const containerElement = document.querySelector(container);

    if (!buttonElement || !containerElement) {
      console.warn(`Button or container element not found for ${button} or ${container}`);
      return;
    }

    
    buttonElement.addEventListener("click", function () {
      categories.forEach(({ container: otherContainer }, otherIndex) => {
        const otherContainerElement = document.querySelector(otherContainer);
        if (otherContainerElement) {
          gsap.to(otherContainerElement, {
            y: otherIndex === index ? 0 : -15,  
            opacity: otherIndex === index ? 1 : 0,
            duration: 0.7,  
            ease: "power2.out"  
          });
        }
      });
    });
  });

}


categoryup();
productroll();
uptext();
selliconup();
productswiper();
down();
downlast();
updateCountdown();


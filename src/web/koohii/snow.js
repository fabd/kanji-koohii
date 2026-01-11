let snowWidth = window.innerWidth;
let snowHeight = window.innerHeight;

class Snowflake {
  constructor() {
    // Create a DOM element for the snowflake
    this.element = document.createElement("div");
    this.element.classList.add("snowflake");
    document.body.appendChild(this.element);

    this.reset(true);
  }

  reset(initial = false) {
    // Randomize position
    this.x = Math.random() * snowWidth;

    // If initial, randomize y anywhere on screen. Otherwise, start just above the viewport
    this.y = initial ? Math.random() * snowHeight : -20;

    // Diameter: 5px to 10px
    const diameter = Math.random() * 5 + 5;
    this.element.style.width = `${diameter}px`;
    this.element.style.height = `${diameter}px`;

    // Random speed falling down
    this.speed = Math.random() * 1 + 0.5; // Speed between 0.5 and 1.5

    // Random horizontal drift (wind)
    this.wind = (Math.random() - 0.5) * 0.5;

    // Random opacity
    this.opacity = Math.random() * 0.6 + 0.3; // Opacity between 0.3 and 0.9
    this.element.style.opacity = this.opacity;
  }

  update() {
    this.y += this.speed;
    this.x += this.wind;

    // Simple sway effect using sine wave
    this.x += Math.sin(this.y / 50) * 0.2;

    // Reset if it goes off the bottom
    if (this.y > snowHeight + 20) {
      this.reset();
    }

    // Wrap around sides
    if (this.x > snowWidth + 20) {
      this.x = -20;
    } else if (this.x < -20) {
      this.x = snowWidth + 20;
    }

    // Move the element using CSS transform for performance
    this.element.style.transform = `translate(${this.x}px, ${this.y}px)`;
  }
}

window.addEventListener("DOMContentLoaded", () => {
  console.log("*** Merry Christmas! ***");

  // Animation Loop
  function animate() {
    for (let flake of snowflakes) {
      flake.update();
    }
    requestAnimationFrame(animate);
  }

  // Snowflake configuration
  const maxSnowflakes = 150; // Total number of flakes
  const snowflakes = [];

  // Handle Window Resize
  window.addEventListener("resize", () => {
    snowWidth = window.innerWidth;
    snowHeight = window.innerHeight;
  });

  // Initialize snowflakes
  for (let i = 0; i < maxSnowflakes; i++) {
    snowflakes.push(new Snowflake());
  }

  animate();
});

<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "music_store");

// Check Connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart functionality
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] == 'add') {
        if (!isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] = 1; // First time adding product
        } else {
            $_SESSION['cart'][$id]++; // Increase quantity on click
        }
        header("Location: products.php");
        exit();
    }
}

// Fetch Products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

// Count total items in cart
$totalItems = array_sum($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>K&P Music Instrument Store</title>
    <link rel="stylesheet" href="css/style.css" />
    <link
      href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css"
      rel="stylesheet"
    />

    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
      integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />

    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"
    />
  </head>
  <body>
    <div id="nav">
      <div class="nav1">
        <div class="logo">
          <img src="assets/home/image/logo.png" alt="" />
        </div>
        <div class="nav-item">
          <ul id="nav-item">
            <a href="#index.hmtl"><li>Home</li> </a>
            <a href="products.php"><li>Product</li> </a>
            <a href="about.html"><li>About Us</li> </a>
            <a href="contact.hmtl"><li>Contact Us</li></a>
            <a href="pages/sign-in.php"><li>Login</li> </a>
          </ul>
        </div>
      </div>
      <div class="nav2">
        <div class="nav2-1">
          <input type="search" placeholder="Search product..." />
          <i class="fa-solid fa-magnifying-glass"></i>
        </div>

        <div class="nav2-icon">
          <i class="fa-regular fa-heart"></i>
          <a href="cart.php" class="cart-link"><i class="fa-solid fa-cart-shopping"></i>(<?php echo $totalItems; ?>)</a>
          <i class="fa-solid fa-user"></i>
        </div>
      </div>
    </div>

    <div class="main">
      <div id="page1">
        <div id="hero-container">
          <div class="swiper mySwiper">
            <div class="swiper-wrapper">
              <div class="swiper-slide slide1">
                <div class="swipe-content slide1-con">
                  <div class="slide1-text">
                    <h1>Find Your Perfect Sound</h1>
                    <p>Professional Music Gear for all Levels</p>
                    <div class="btns">
                      <a href="products.php"><button id="shop-now" data-hover="Shop Now">
                        Shop Now
                      </button></a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="swiper-slide slide2">
                <div class="swipe-content slide2-con">
                  <div class="slide2-text">
                    <h3>music makes you fly</h3>
                    <h1>
                      best <br />
                      Instrument <br />
                      store
                    </h1>
                    <h5>Your Sound , Our Passion</h5>
                  </div>
                </div>
              </div>
              <div class="swiper-slide slide3">
                <div class="swipe-content slide3-con">
                  <div class="img-con">
                    <img
                      src="assets/home/image/ksck_vhac_141113-removebg.png"
                      alt=""
                    />
                  </div>
                  <div class="slide3-text">
                    <h3>music makes you fly</h3>
                    <h1>
                      Where music <br />
                      comes to <br />
                      life
                    </h1>
                    <h5>Explore a World of Sound and Rhythm</h5>
                  </div>
                </div>
              </div>
            </div>

            <div class="swiper-pagination"></div>
            <div class="autoplay-progress">
              <svg viewBox="0 0 48 48">
                <circle cx="24" cy="24" r="20"></circle>
              </svg>
              <span></span>
            </div>
          </div>
        </div>
      </div>

      <div id="page2">
        <div class="shop-cat">
          <div class="cat-head">
            <h1>Our Collections</h1>

          </div>
          <div class="product-swipe">
            <button class="swiper-button prev-btn">
              <i class="ri-arrow-left-s-line"></i>
            </button>
            <div class="product-list">
              <div class="product-item">
                <div class="product-img">
                  <img src="assets/home/product-img/piano.jpg" alt="Piano" />
                </div>
                <div class="product-text">
                  <h1>Piano</h1>
                  <a href="products.php"><button class="shop-btn">Shop Now</button></a>
                </div>
              </div>
              <div class="product-item">
                <div class="product-img">
                  <img src="assets/home/product-img/violin.jpg" alt="Violin" />
                </div>
                <div class="product-text">
                  <h1>Violin</h1>
                  <button class="shop-btn">Shop Now</button>
                </div>
              </div>
              <div class="product-item">
                <div class="product-img">
                  <img src="assets/home/product-img/drumset.jpg" alt="Guitar" />
                </div>
                <div class="product-text">
                  <h1>DrumSet</h1>
                  <a href="products.php"><button class="shop-btn">Shop Now</button></a>
                </div>
              </div>
              <div class="product-item">
                <div class="product-img">
                  <img src="assets/home/product-img/trumpet.jpg" alt="Drum" />
                </div>
                <div class="product-text">
                  <h1>Trumpet</h1>
                  <a href="products.php"><button class="shop-btn">Shop Now</button></a>
                </div>
              </div>

              <div class="product-item">
                <div class="product-img">
                  <img
                    src="assets/home/product-img/electric-guitar.jpg"
                    alt="Flute"
                  />
                </div>
                <div class="product-text">
                  <h1>Electric-Guitar</h1>
                  <a href="products.php"><button class="shop-btn">Shop Now</button></a>
                </div>
              </div>

              <div class="product-item">
                <div class="product-img">
                  <img src="assets/home/product-img/tabla.jpg" alt="Flute" />
                </div>
                <div class="product-text">
                  <h1>Tabla</h1>
                  <a href="products.php"><button class="shop-btn">Shop Now</button></a>
                </div>
              </div>
            </div>
            <button class="swiper-button next-btn">
              <i class="ri-arrow-right-s-line"></i>
            </button>
          </div>
        </div>
        <div class="line"></div>

        <div class="best-sell">
          <h1>best seller</h1>
          <div class="seller-list">
            <div class="sell-item">
              <div class="sell-img">
                <img
                  src="assets/home/products/18-Pipes-Pan-Flute-F-Key-1.webp"
                  alt="Piano"
                />
                <img
                  id="hover"
                  src="assets/home/products/hover-18-Pipes-Pan-Flute-F-Key-2.webp"
                  alt=""
                />
              </div>
              <div class="sell-text">
                <a href="#"> 18 Pipes Pan Flute F Key</a>
                <p>₹500 <span>₹1000 </span></p>
              </div>
              <div class="sell-icon">
                <i class="ri-shopping-bag-4-line"></i>
                <i class="ri-search-line"></i>
                <i class="ri-heart-line"></i>
              </div>
            </div>

            <div class="sell-item">
              <div class="sell-img">
                <img
                  src="assets/home/products/Jaguar-Electric-Guitar.webp"
                  alt="Piano"
                />

                <img
                  id="hover"
                  src="assets/home/products/hover-Jaguar-Electric-Guitar-2.webp"
                  alt=""
                />
              </div>
              <div class="sell-text">
                <a href="#">70s Jaguar-Electric-Guitar</a>
                <p>₹5000 <span>₹7500 </span></p>
              </div>
              <div class="sell-icon">
                <i class="ri-shopping-bag-4-line"></i>
                <i class="ri-search-line"></i>
                <i class="ri-heart-line"></i>
              </div>
            </div>

            <div class="sell-item">
              <div class="sell-img">
                <img
                  src="assets/home/products/Weighted-Action-Key-Digital-Piano-2.webp"
                  alt="Piano"
                />

                <img
                  id="hover"
                  src="assets/home/products/hover-Weighted-Action-Key-Digital-Piano-2.webp"
                  alt=""
                />
              </div>
              <div class="sell-text">
                <a href="#"> 88-Key Digital Piano</a>
                <p>₹2500 <span>₹3500</span></p>
              </div>
              <div class="sell-icon">
                <i class="ri-shopping-bag-4-line"></i>
                <i class="ri-search-line"></i>
                <i class="ri-heart-line"></i>
              </div>
            </div>

            <div class="sell-item">
              <div class="sell-img">
                <img
                  src="assets/home/products/Affordable-Home-Piano.webp"
                  alt="Piano"
                />

                <img
                  id="hover"
                  src="assets/home/products/hover-Affordable-Home-Piano.webp"
                  alt=""
                />
              </div>
              <div class="sell-text">
                <a href="#">Affordable Home Piano</a>
                <p>₹10000 <span>₹12000 </span></p>
              </div>
              <div class="sell-icon">
                <i class="ri-shopping-bag-4-line"></i>
                <i class="ri-search-line"></i>
                <i class="ri-heart-line"></i>
              </div>
            </div>

            <div class="sell-item">
              <div class="sell-img">
                <img
                  src="assets/home/products/AS-400-Alto-Saxophone.webp"
                  alt="Piano"
                />

                <img
                  id="hover"
                  src="assets/home/products/hover-AS-400-Alto-Saxophone-2.webp"
                  alt=""
                />
              </div>
              <div class="sell-text">
                <a href="#">AS-400 Alto Saxophone</a>
                <p>₹7000 <span>₹9500 </span></p>
              </div>
              <div class="sell-icon">
                <i class="ri-shopping-bag-4-line"></i>
                <i class="ri-search-line"></i>
                <i class="ri-heart-line"></i>
              </div>
            </div>

            <div class="sell-item">
              <div class="sell-img">
                <img
                  src="assets/home/products/Beginner-Classical-Guitar-2.webp"
                  alt="Piano"
                />

                <img
                  id="hover"
                  src="assets/home/products/hover-Beginner-Classical-Guitar-2.webp"
                  alt=""
                />
              </div>
              <div class="sell-text">
                <a href="#">Beginner's Classical Guitar</a>
                <p>₹4000 <span>₹7000 </span></p>
              </div>
              <div class="sell-icon">
                <i class="ri-shopping-bag-4-line"></i>
                <i class="ri-search-line"></i>
                <i class="ri-heart-line"></i>
              </div>
            </div>

            <div class="sell-item">
              <div class="sell-img">
                <img
                  src="assets/home/products/Vertical-Bamboo-Flute.webp"
                  alt="Piano"
                />

                <img
                  id="hover"
                  src="assets/home/products/hover-Vertical-Bamboo-Flute-2.webp"
                  alt=""
                />
              </div>
              <div class="sell-text">
                <a href="#">Brown Vertical Bamboo Flute</a>
                <p>₹700<span>₹1000 </span></p>
              </div>
              <div class="sell-icon">
                <i class="ri-shopping-bag-4-line"></i>
                <i class="ri-search-line"></i>
                <i class="ri-heart-line"></i>
              </div>
            </div>

            <div class="sell-item">
              <div class="sell-img">
                <img
                  src="assets/home/products/Western-Cutaway-Style-with-6-Strings-1.webp"
                  alt="Piano"
                />

                <img
                  id="hover"
                  src="assets/home/products/hover-Western-Cutaway-Style-with-6-Strings-2.webp"
                  alt=""
                />
              </div>
              <div class="sell-text">
                <a href="#"> Cutway Style With 6 Strings</a>
                <p>₹5000 <span>₹10000 </span></p>
              </div>
              <div class="sell-icon">
                <i class="ri-shopping-bag-4-line"></i>
                <i class="ri-search-line"></i>
                <i class="ri-heart-line"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div id="banner">
        <div class="banner-container">
          <div class="banner-text">
            <h1>Sale event</h1>
            <h3>Save on all selling and exclusive styles</h3>
          </div>

          <div class="counter">
            <div class="time-unit">
              <div class="circle" id="days">--</div>
              <div class="label">Days</div>
            </div>
            <div class="time-unit">
              <div class="circle" id="hours">--</div>
              <div class="label">Hours</div>
            </div>
            <div class="time-unit">
              <div class="circle" id="minutes">--</div>
              <div class="label">Mins</div>
            </div>
            <div class="time-unit">
              <div class="circle" id="seconds">--</div>
              <div class="label">Secs</div>
            </div>
          </div>

          <div class="banner-btn">
            <button>Buy Now !</button>
          </div>
        </div>
      </div>

      <div id="page3">
        <div class="page3-title">
          <div class="t1">
            <h3>Our Product</h3>
            <h1>Discover Our Collections</h1>
          </div>
          <button class="t2">
            Explore all collections <i class="ri-arrow-right-line"></i>
          </button>
        </div>

        <div class="page3-product">
          <div class="product-container">
            <div class="p-text">
              <h1>01</h1>
            </div>
            <div class="p-img">
              <img
                src="assets/home/product-img/guitar-shop-acoustic.png"
                alt=""
              />
            </div>
          </div>

          <div class="product-container">
            <div class="p-text">
              <h1>02</h1>
            </div>
            <div class="p-img">
              <img
                src="assets/home/product-img/guitar-shop-electric.png"
                alt=""
              />
            </div>
          </div>

          <div class="product-container">
            <div class="p-text">
              <h1>03</h1>
            </div>
            <div class="p-img">
              <img src="assets/home/product-img/guitar-shop-bass.png" alt="" />
            </div>
          </div>

          <div class="product-container">
            <div class="p-text">
              <h1>04</h1>
            </div>
            <div class="p-img">
              <img src="assets/home/product-img/guitar-shop-uklea.png" alt="" />
            </div>
          </div>
        </div>
      </div>

      <div id="page4">
        <div class="page4-left">
          <h1>our story</h1>
          <h3>
            Embracing a Musical <br />
            Revolution
          </h3>
          <div class="music-svg">
            <img src="assets/home/image/music-info.webp" alt="" />
          </div>
          <p>
            Discover a world of music with our carefully curated collections.
            From the soulful strings of our Guitar Gallery to the elegant
            harmonies of our Violin Vault, and the resonant depths of our Cello
            Corner, we have the perfect instrument to match your passion and
            skill. Explore all our collections and find the instrument that
            speaks to you at our musical instrument shop.
          </p>
          <button>Shop Now</button>
        </div>
        <div class="page4-right">
          <div class="right-image-one">
            <img src="assets/home/image/canva-female-musician.webp" alt="" />
          </div>
          <div class="right-image-two">
            <img src="assets/home/image/guitarbg.webp" alt="" />
          </div>
        </div>
      </div>

      <div id="page5">
        <div class="page5-container">
          <div class="page5-left">
            <h1>
              Where words fail, music speaks. <br />
              <span
                >Through every chord and melody, music has the power to heal,
                inspire, and connect us all.</span
              >
            </h1>
            <p>
              At K&P Music, we understand that music is the language of the
              soul. Our collection of instruments is carefully curated to help
              you express what words cannot. Whether you’re just beginning or
              are a seasoned musician, we offer the tools to bring your music to
              life and share it with the world. Let music be your voice!
            </p>
          </div>
          <div class="page5-left">
            <div class="page5-input">
              <input type="email" name="" placeholder="Enter your email...." />
              <i class="ri-send-plane-fill"></i>
            </div>
          </div>
        </div>
      </div>

      <div id="footer">
        <div class="foot-top">
          <div class="foot-line"></div>
          <div class="foot-icon">
            <i class="ri-truck-line"></i>free home delivery
          </div>
          <div class="foot-icon">
            <i class="ri-shield-keyhole-fill"></i>secured payment
          </div>
          <div class="foot-icon">
            <i class="ri-time-line"></i>on time delivery
          </div>
          <div class="foot-line"></div>
        </div>
        <div class="foot-bottom">
          <div class="foot-bottom-items">
            <h4>info</h4>
            <p>Contact Us</p>
            <p>Order tracking</p>
            <p>Customer Service</p>
            <p>F.A.Q's</p>
          </div>

          <div class="foot-bottom-items">
            <h4>policies</h4>
            <p>Shipping Policy</p>
            <p>Return Policy</p>
            <p>Privacy Policy</p>
            <p>Terms & Conditions</p>
          </div>
          <div class="foot-bottom-items">
            <h4>Services</h4>
            <p>Privacy Policy</p>
            <p>Your Account</p>
            <p>Terms & Conditions</p>
            <p>Contact Us</p>
          </div>

          <div class="foot-bottom-items">
            <h4>Account</h4>
            <p>About Us</p>
            <p>Terms & Conditions</p>
            <p>Privacy Policy</p>
            <p>Contact Us</p>
          </div>

          <div class="foot-bottom-items">
            <h1>Newsletter</h1>
            <p>
              Subscribe to our newsletter and get 10% off your first purchase
            </p>
            <input type="email" name="" placeholder="Enter your email...." />
            <button>Subscribe</button>
            <div class="foot-social">
              <i class="ri-facebook-fill"></i>
              <i class="ri-instagram-fill"></i>
              <i class="ri-twitter-fill"></i>
              <i class="ri-youtube-fill"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <script src="js/app.js"></script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
      crossorigin="anonymous"
    ></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
  </body>
</html>
<?php $conn->close(); ?>
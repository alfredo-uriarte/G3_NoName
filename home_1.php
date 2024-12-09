<?php
include_once 'includes/header.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT name, description, price, image_url FROM products ORDER BY created_at DESC LIMIT 3";
$result = $conn->query($sql);
$products = [];
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $products[] = $row;
  }
} else {
  echo "No products found.";
}

$conn->close();
?>


<!-- header -->


<!-- main section -->
  <div class="mainSection-first">
    <img src="images/homepage-background.jpg" alt="background image of our home page">
    <div class="centered">
      <h3>Made for book-loving humans</h3>
      <button><a href="index_1.php">Shop Now</a></button>
    </div>
  </div>

  <div class="mainSection-second">
    <h2>Trending Books</h2>
    <div class="product-grid">
      <?php foreach ($products as $product): ?>
        <div class="product-card">
          <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
            alt="<?php echo htmlspecialchars($product['name']); ?>">
          <h3><?php echo htmlspecialchars($product['name']); ?></h3>
          <p><?php echo htmlspecialchars($product['description']); ?></p>
          <span>$<?php echo htmlspecialchars($product['price']); ?></span>
          <button class="buy-now"><a href="index_1.php">Shop Now</a></button>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="mainSec">
    <div class="mainSection-left">
      <h2>Welcome to</h2>
      <h1>PageTurners!</h1>
      <h3>The book store that you find wide variety of your interests</h3>
      <p>
        your one-stop destination for all things literary! Whether you’re a
        fan of gripping mysteries, heartwarming romances, thrilling sci-fi
        adventures, or insightful non-fiction, our shelves are brimming with
        stories waiting to captivate your imagination. At PageTurners, we
        believe every book opens a new world of possibilities, and our mission
        is to connect you with the ones that inspire, entertain, and enrich
        your life. Explore our carefully curated collections, discover hidden
        gems, and immerse yourself in the joy of reading. Your next great
        adventure begins here—happy reading!
      </p>
      <!-- <button>See Trending Books</button> -->

      <div class="botn botn-one">
        <span class="mainSection-leftButton"><a href="index_1.php">See Trending Books</a></span>
      </div>
    </div>
    <div class="mainSection-right">
      <img src="images/welcome-image.png" alt="image of a book used in the home page" />
    </div>
  </div>

  <!-- video wrapper-->
  <div class="video-wrapper">
    <video class="background" autoplay muted loop>
      <source src="images/OdysseyFinalVideo.mp4" type="video/mp4" />
    </video>
    <div class="content">
      <h2>"A room without books is like a body without a soul." - Marcus Tullius Cicero</h2>
    </div>
  </div>


<?php
include_once 'includes/footer.php';
?>
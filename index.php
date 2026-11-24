<?php
// index.php - Home page
$base = '';
require_once 'includes/db.php';

// Three featured rooms (highest priced available ones)
$featured = $pdo->query("SELECT * FROM rooms WHERE status = 'Available' ORDER BY price_per_night DESC LIMIT 3")->fetchAll();

// Latest guest reviews with the guest name
$reviews = $pdo->query("SELECT r.rating, r.comment, u.full_name
                        FROM reviews r JOIN users u ON r.user_id = u.id
                        ORDER BY r.created_at DESC, r.id DESC LIMIT 3")->fetchAll();
$avg = $pdo->query("SELECT AVG(rating) AS a, COUNT(*) AS c FROM reviews")->fetch();

$page_title = 'Home';
include 'includes/header.php';
?>
<section class="hero">
    <div class="container">
        <h1>A quiet villa between the lagoon and the sea</h1>
        <p>Stay in Negombo, ten minutes from Browns Beach and a short drive from Bandaranaike International Airport.</p>
        <a class="btn btn-brown" href="rooms.php">Book Now</a>

        <form class="search-bar date-search" action="rooms.php" method="get">
            <div class="field"><label for="check_in">Check-in</label><input type="date" id="check_in" name="check_in"></div>
            <div class="field"><label for="check_out">Check-out</label><input type="date" id="check_out" name="check_out"></div>
            <div class="field"><label for="guests">Guests</label><input type="number" id="guests" name="guests" min="1" max="10" value="2"></div>
            <div class="field"><button class="btn" type="submit">Check availability</button></div>
        </form>
    </div>
</section>

<section>
    <div class="container">
        <h2>Welcome to Lagoon Breeze Villa</h2>
        <p>Lagoon Breeze Villa is a small family-run guest house in Negombo with six rooms, a shaded garden and a rooftop
        terrace where you can watch fishing boats return in the evening. Breakfast is cooked fresh every morning, with
        string hoppers, coconut sambol and fresh tropical fruit.</p>
        <div class="grid">
            <div class="highlight"><h3>Browns Beach</h3><p>A 10 minute tuk-tuk ride away. Good for sunsets and fresh seafood on the sand.</p></div>
            <div class="highlight"><h3>Dutch Canal</h3><p>Built by the Dutch and still used by boats. Walk or cycle along the banks in the early morning.</p></div>
            <div class="highlight"><h3>BIA Airport</h3><p>About 20 minutes by road, so it is easy for a first or last night in Sri Lanka.</p></div>
        </div>
    </div>
</section>

<section class="alt-bg">
    <div class="container">
        <h2>Our facilities</h2>
        <div class="grid">
            <div class="highlight">Free Wi-Fi in all rooms</div>
            <div class="highlight">Breakfast with Sri Lankan and Western choices</div>
            <div class="highlight">Garden and rooftop terrace</div>
            <div class="highlight">Airport pickup on request</div>
            <div class="highlight">Secure parking</div>
            <div class="highlight">Laundry service</div>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <h2>Featured rooms</h2>
        <div class="grid">
            <?php foreach ($featured as $room) { room_card($room); } ?>
        </div>
        <p><a class="btn btn-brown" href="rooms.php">See all rooms</a></p>
    </div>
</section>

<section class="alt-bg">
    <div class="container">
        <h2>Guest reviews
            <?php if ($avg['c'] > 0): ?>
                <span class="small">(average <?php echo round($avg['a'], 1); ?> / 5 from <?php echo (int)$avg['c']; ?> reviews)</span>
            <?php endif; ?>
        </h2>
        <?php if (count($reviews) == 0): ?>
            <p>No reviews yet. Be the first to stay with us!</p>
        <?php endif; ?>
        <div class="grid">
            <?php foreach ($reviews as $rv): ?>
                <div class="card"><div class="card-body">
                    <div class="stars"><?php echo str_repeat('★', (int)$rv['rating']) . str_repeat('☆', 5 - (int)$rv['rating']); ?></div>
                    <p><?php echo e($rv['comment']); ?></p>
                    <p class="small"><strong><?php echo e($rv['full_name']); ?></strong></p>
                </div></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include 'includes/footer.php'; ?>

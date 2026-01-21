<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Kitchen Screen Demo</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Font Awesome (for icons) -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
  />
  <style>
    /* Container to hold the top status buttons (Open, Done, On Hold) */
    .status-bar {
      margin: 1rem 0;
      display: flex;
      gap: 1rem;
      justify-content: center;
    }
    .status-bar button {
      width: 150px;
      font-size: 1rem;
      font-weight: 600;
    }

    /* Grid of order cards */
    .orders-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 1rem;
    }

    /* Each order card */
    .order-card {
      border: 1px solid #ddd;
      border-radius: 5px;
      background-color: #fff;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .order-card-header {
      color: #fff;
      font-weight: 600;
      padding: 0.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    /* Example color stripes: open=blue, done=green, on-hold=orange, etc. */
    .order-card-header.open {
      background-color: #0d6efd; /* bootstrap .bg-primary */
    }
    .order-card-header.done {
      background-color: #198754; /* .bg-success */
    }
    .order-card-header.hold {
      background-color: #fd7e14; /* .bg-warning or orange variant */
    }

    /* The body with order details */
    .order-body {
      padding: 0.75rem;
      flex: 1;
    }
    .order-body small {
      color: #666;
    }
    .item-list {
      margin-top: 0.5rem;
      margin-bottom: 0.5rem;
    }
    .item-list li {
      margin-bottom: 0.25rem;
    }

    /* Footer with Done / Hold buttons */
    .order-footer {
      padding: 0.5rem;
      display: flex;
      justify-content: space-around;
      border-top: 1px solid #eee;
    }
    .order-footer button {
      min-width: 70px;
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <!-- Navigation bar / Menu icon (optional) -->
  <nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button">
        <span class="navbar-toggler-icon"></span>
      </button>
      <span class="navbar-text text-white fw-bold">
        Kitchen Screen
      </span>
    </div>
  </nav>

  <!-- Status bar for order counts -->
  <div class="status-bar">
    <button class="btn btn-primary">
      <span>8 Open</span>
    </button>
    <button class="btn btn-success">
      <span>9 Done</span>
    </button>
    <button class="btn btn-warning text-dark">
      <span>0 On Hold</span>
    </button>
  </div>

  <!-- Orders Grid -->
  <div class="orders-grid">
    <!-- Sample Card #1 (Open) -->
    <div class="order-card">
      <div class="order-card-header open">
        <div>#012623728400<br>Abdullah Ibrahim</div>
        <div>11:27</div>
      </div>
      <div class="order-body">
        <small>Pickup / Walk-In</small>
        <ul class="item-list">
          <li>1 &times; Pepperoni Pizza <br><small>cheese, extra sauce</small></li>
          <li>2 &times; Bolognese pasta</li>
          <li>1 &times; Ravioli with spinach</li>
          <li>1 &times; Lasagne with chicken</li>
        </ul>
      </div>
      <div class="order-footer">
        <button class="btn btn-secondary">Done</button>
        <button class="btn btn-outline-secondary">Hold</button>
      </div>
    </div>

    <!-- Sample Card #2 (Open) -->
    <div class="order-card">
      <div class="order-card-header open">
        <div>#012623730580<br>Abdullah Ibrahim</div>
        <div>06:38</div>
      </div>
      <div class="order-body">
        <small>Pickup / Russell Stoner</small>
        <ul class="item-list">
          <li>2 &times; New York strip</li>
          <li>1 &times; BBQ beef burger <br><small>No onions</small></li>
          <li>1 &times; Pork Chops</li>
          <li>3 &times; Mozzarella sticks</li>
        </ul>
      </div>
      <div class="order-footer">
        <button class="btn btn-secondary">Done</button>
        <button class="btn btn-outline-secondary">Hold</button>
      </div>
    </div>

    <!-- Sample Card #3 (Open) -->
    <div class="order-card">
      <div class="order-card-header open">
        <div>#012623748254<br>Rustem Iskandar</div>
        <div>05:26</div>
      </div>
      <div class="order-body">
        <small>Delivery / Sarah Bauman</small>
        <ul class="item-list">
          <li>2 &times; Cappuccino <br><small>Oat milk</small></li>
          <li>3 &times; Cheesecake</li>
          <li>1 &times; Hazelnut Mocha</li>
          <li>3 &times; Muffin</li>
        </ul>
      </div>
      <div class="order-footer">
        <button class="btn btn-secondary">Done</button>
        <button class="btn btn-outline-secondary">Hold</button>
      </div>
    </div>

    <!-- Sample Card #4 (Done) -->
    <div class="order-card">
      <div class="order-card-header done">
        <div>#012623760952<br>Catherine Wills...</div>
        <div>03:50</div>
      </div>
      <div class="order-body">
        <small>Delivery / Matthew Handson</small>
        <ul class="item-list">
          <li>1 &times; Classic Beyond Burger <br><small>Extra sauce</small></li>
          <li>2 &times; Falafel and Hummus <br><small>No salt</small></li>
          <li>3 &times; Grilled vegetables</li>
          <li>2 &times; Pasta and Vegan...</li>
        </ul>
      </div>
      <div class="order-footer">
        <button class="btn btn-secondary">Done</button>
        <button class="btn btn-outline-secondary">Hold</button>
      </div>
    </div>

    <!-- Sample Card #5 (Hold) -->
    <div class="order-card">
      <div class="order-card-header hold">
        <div>#012623779121<br>Rustem Iskandar</div>
        <div>02:33</div>
      </div>
      <div class="order-body">
        <small>Quicksale / Oliver Dark</small>
        <ul class="item-list">
          <li>2 &times; Egg Flower soup<br><small>Sweet and sour sauce</small></li>
          <li>3 &times; Fried shrimp</li>
          <li>1 &times; Fried rice <br><small>No peanuts</small></li>
          <li>2 &times; Honey chicken</li>
        </ul>
      </div>
      <div class="order-footer">
        <button class="btn btn-secondary">Done</button>
        <button class="btn btn-outline-secondary">Hold</button>
      </div>
    </div>

    <!-- ... Add more cards as needed ... -->
  </div>
</div>

<!-- Optional: Bootstrap JS for responsive layout and potential modals -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/*
  In a real app, you’d:
   1) Load orders dynamically via AJAX or WebSocket.
   2) Update the “Open / Done / On Hold” counts automatically.
   3) Wire up “Done / Hold” buttons to your server logic.
*/
</script>
</body>
</html>

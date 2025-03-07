<?php
session_start();
include 'db.php';

// Fetch testimonials
$query = "SELECT * FROM testimonials ORDER BY created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonials</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .testimonial-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #fff;
            transition: box-shadow 0.3s;
        }
        .testimonial-card:hover {
            transform: scale(1.05); 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
        }
        .testimonial-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .testimonial-header h5 {
            margin: 0;
            margin-right: 10px;
            font-weight: bold;
        }
        .testimonial-date {
            font-size: 0.9em;
            color: #6c757d;
        }
        .container {
            color: #333;
            max-width: 800px;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-4 mb-4 text-center">User Testimonials</h1>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <h5><?php echo htmlspecialchars($row['name']); ?></h5>
                        <span class="testimonial-date"><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['created_at']))); ?></span>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info text-center" role="alert">
                No testimonials found.
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center">
    <a href="index.php" class="btn btn-secondary mt-4">Return</a>
        </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

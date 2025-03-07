<?php foreach ($categorized_vehicles as $purpose => $vehicles): ?>
            <h2><?php echo ucfirst(htmlspecialchars($purpose)); ?> Vehicles</h2>
            <div class="row">
                <?php foreach ($vehicles as $vehicle): ?>
                    <div class="col-md-4">
                        <div class="vehicle-card">
                            <img src="<?php echo htmlspecialchars($vehicle['vehicle_image']); ?>" alt="Vehicle Image">
                            <div class="vehicle-info">
                                <h3><?php echo htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']); ?></h3>
                                <p><strong>Plate Number:</strong> <?php echo htmlspecialchars($vehicle['plate_number']); ?></p>
                                <p><strong>Driver:</strong> <?php echo htmlspecialchars($vehicle['driver_name']); ?></p>
                                <p><strong>Status:</strong> <?php echo ucfirst(htmlspecialchars($vehicle['status'])); ?></p>
                                <p><strong>Price:</strong> ₱<?php echo number_format(htmlspecialchars($vehicle['price']), 2); ?></p>
                                <p><strong>Purpose:</strong> <?php echo htmlspecialchars($vehicle['purpose']); ?></p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($vehicle['description']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    
    <!-- Add Vehicle Modal -->
    <div class="modal fade" id="addForm" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Vehicle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="plate_number">Plate Number</label>
                            <input type="text" class="form-control" name="plate_number" required>
                        </div>
                        <div class="form-group">
                            <label for="make">Make</label>
                            <input type="text" class="form-control" name="make" required>
                        </div>
                        <div class="form-group">
                            <label for="model">Model</label>
                            <input type="text" class="form-control" name="model" required>
                        </div>
                        <div class="form-group">
                            <label for="year">Year</label>
                            <input type="text" class="form-control" name="year" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" class="form-control" name="price" required>
                        </div>
                        <div class="form-group">
                            <label for="driver_name">Driver Name</label>
                            <input type="text" class="form-control" name="driver_name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="purpose">Purpose</label>
                            <select class="form-control" name="purpose" required>
                                <option value="family outing">Family Outing</option>
                                <option value="wedding">Wedding</option>
                                <option value="road trip">Road Trip</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="vehicle_image">Upload Image</label>
                            <input type="file" class="form-control-file" name="vehicle_image" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" name="add_vehicle" class="btn btn-primary">Add Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
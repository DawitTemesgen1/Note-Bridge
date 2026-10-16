
</main><!-- end .main-content -->

<!-- ==================== FOOTER ==================== -->
<footer class="footer">
    <div class="container">
        <!-- Institutional Attribution Bar -->
        <div class="footer-institution-badge">
            <div class="footer-inst-item">
                <i class="fas fa-university"></i>
                <span><strong>Jimma University</strong></span>
            </div>
            <span class="footer-inst-divider">&bull;</span>
            <div class="footer-inst-item">
                <i class="fas fa-school"></i>
                <span><strong>Jimma Institute of Technology (JiT)</strong></span>
            </div>
            <span class="footer-inst-divider">&bull;</span>
            <div class="footer-inst-item">
                <i class="fas fa-laptop-code"></i>
                <span><strong>Computer Science Department</strong></span>
            </div>
        </div>

        <div class="footer-grid">
            <!-- Brand column -->
            <div class="footer-col">
                <h3><i class="fas fa-book-open"></i> NoteBridge</h3>
                <p>A collaborative academic platform for university study notes. Upload, discover, and download quality materials organised by department and course.</p>
            </div>

            <!-- Quick links -->
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?php echo $basePath; ?>/index.php">Home</a></li>
                    <li><a href="<?php echo $basePath; ?>/pages/browse.php">Browse Notes</a></li>
                    <li><a href="<?php echo $basePath; ?>/pages/upload.php">Upload Notes</a></li>
                    <li><a href="<?php echo $basePath; ?>/pages/about.php">About Us</a></li>
                </ul>
            </div>

            <!-- Contact info -->
            <div class="footer-col">
                <h4>Contact</h4>
                <ul>
                    <li><i class="fas fa-user-graduate"></i> CS Evening Program 4th Year Students</li>
                    <li><i class="fas fa-calendar-alt"></i> Class of 2016 E.C.</li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> NoteBridge. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Our main JavaScript file -->
<script src="<?php echo $basePath; ?>/js/main.js"></script>
</body>
</html>

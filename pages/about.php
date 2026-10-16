<?php
/**
 * ============================================================
 * about.php — About NoteBridge
 * ============================================================
 */

$pageTitle = 'About Us';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="about-content">
        <div class="page-header text-center">
            <h1><i class="fas fa-info-circle"></i> About NoteBridge</h1>
            <p>Bridging the gap between students and quality study materials.</p>
        </div>

        <h2>Our Mission</h2>
        <p>NoteBridge was born from a simple idea: students helping students. We believe that sharing knowledge should be effortless. Our platform connects university students across departments, enabling them to upload, discover, and download study notes — completely free.</p>

        <h2>How It Works</h2>
        <p><strong>1. Sign Up:</strong> Create a free account in seconds.</p>
        <p><strong>2. Upload:</strong> Share your study notes as PDF files. Choose the department, course, and academic year.</p>
        <p><strong>3. Discover:</strong> Browse or search notes using powerful filters by department, course, year, or keyword.</p>
        <p><strong>4. Download:</strong> Get the notes you need instantly, free of charge.</p>
        <p><strong>5. Rate:</strong> Help the community by rating the quality of notes you've used.</p>

        <h2>Technology & Academic Context</h2>
        <p>NoteBridge is developed at <strong>Jimma University</strong>, <strong>Jimma Institute of Technology (JiT)</strong> in the <strong>Computer Science Department</strong> using industry-standard web technologies:</p>
        <ul style="list-style: disc; padding-left: var(--space-8); margin-bottom: var(--space-4);">
            <li><strong>Frontend:</strong> HTML5, CSS3, Vanilla JavaScript</li>
            <li><strong>Backend:</strong> PHP</li>
            <li><strong>Database:</strong> MySQL</li>
            <li><strong>Server:</strong> XAMPP (Apache + MySQL)</li>
        </ul>

        <h2>Meet the Project Team</h2>
        <p>NoteBridge was developed by Computer Science students at Jimma University, Jimma Institute of Technology (JiT):</p>

        <div class="team-grid">
            <div class="card team-card">
                <div class="team-avatar"><i class="fas fa-user-graduate"></i></div>
                <h4>Aziza Mohamed</h4>
                <div class="team-id-badge">ID: EU 0529/16</div>
                <p>Computer Science</p>
            </div>
            <div class="card team-card">
                <div class="team-avatar"><i class="fas fa-user-graduate"></i></div>
                <h4>Dawit Temesgen</h4>
                <div class="team-id-badge">ID: EU 0535/16</div>
                <p>Computer Science</p>
            </div>
            <div class="card team-card">
                <div class="team-avatar"><i class="fas fa-user-graduate"></i></div>
                <h4>Eyob Wondeafrash</h4>
                <div class="team-id-badge">ID: EU 0538/16</div>
                <p>Computer Science</p>
            </div>
            <div class="card team-card">
                <div class="team-avatar"><i class="fas fa-user-graduate"></i></div>
                <h4>Kidus Kalid</h4>
                <div class="team-id-badge">ID: EU 0551/16</div>
                <p>Computer Science</p>
            </div>
            <div class="card team-card">
                <div class="team-avatar"><i class="fas fa-user-graduate"></i></div>
                <h4>Yosef Birhanu</h4>
                <div class="team-id-badge">ID: EU 0577/16</div>
                <p>Computer Science</p>
            </div>
        </div>

        <div class="team-roster-section" style="margin-top: var(--space-8);">
            <h3>Project Members Roster</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Student ID</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>1</strong></td>
                            <td><strong>Aziza Mohamed</strong></td>
                            <td><span class="badge badge-info">EU 0529/16</span></td>
                            <td>Computer Science</td>
                        </tr>
                        <tr>
                            <td><strong>2</strong></td>
                            <td><strong>Dawit Temesgen</strong></td>
                            <td><span class="badge badge-info">EU 0535/16</span></td>
                            <td>Computer Science</td>
                        </tr>
                        <tr>
                            <td><strong>3</strong></td>
                            <td><strong>Eyob Wondeafrash</strong></td>
                            <td><span class="badge badge-info">EU 0538/16</span></td>
                            <td>Computer Science</td>
                        </tr>
                        <tr>
                            <td><strong>4</strong></td>
                            <td><strong>Kidus Kalid</strong></td>
                            <td><span class="badge badge-info">EU 0551/16</span></td>
                            <td>Computer Science</td>
                        </tr>
                        <tr>
                            <td><strong>5</strong></td>
                            <td><strong>Yosef Birhanu</strong></td>
                            <td><span class="badge badge-info">EU 0577/16</span></td>
                            <td>Computer Science</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

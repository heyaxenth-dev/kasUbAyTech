    <?php 
   
    // Function to check if page exists, fallback to page-error-404.html if not
    function get_page_link($page_name) {
        $file_path = $page_name . '.php';
        if (file_exists($file_path)) {
           return $file_path;
        }else {
            return 'pages-error-404.html';
        }
    }

    
    ?>


    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">

        <ul class="sidebar-nav" id="sidebar-nav">

            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link<?= ($current_page == 'homepage') ? '' : ' collapsed' ?>"
                    href="<?= get_page_link('homepage') ?>">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Manage Questions -->
            <li class="nav-item">
                <a class="nav-link<?= ($current_page == 'questions') ? '' : ' collapsed' ?>"
                    href="<?= get_page_link('questions') ?>">
                    <i class="bi bi-question-circle"></i>
                    <span>Manage Questions</span>
                </a>
            </li>

            <!-- Assessment Results -->
            <li class="nav-item">
                <a class="nav-link<?= ($current_page == 'results') ? '' : ' collapsed' ?>"
                    href="<?= get_page_link('results') ?>">
                    <i class="bi bi-clipboard-data"></i>
                    <span>Assessment Results</span>
                </a>
            </li>

            <!-- Compatibility Scores -->
            <li class="nav-item">
                <a class="nav-link<?= ($current_page == 'compatibility') ? '' : ' collapsed' ?>"
                    href="<?= get_page_link('compatibility') ?>">
                    <i class="bi bi-graph-up"></i>
                    <span>Compatibility Scores</span>
                </a>
            </li>

        </ul>

    </aside>
    <!-- End Sidebar -->
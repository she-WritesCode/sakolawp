<?php get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <?php
        while (have_posts()) :
            the_post();
        ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header text-center p-4 md:p-8 min-h-48">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

                    <button>
                        <div class="flex gap-2 items-center">
                            <span>Enroll Now</span>
                            <i class="pi pi-arrow-right"></i>
                        </div>
                    </button>
                </header>

                <div class="my-8 entry-content">
                    <?php the_content(); ?>
                </div>
                <div class="flex flex-col md:flex-row gap-4 md:gap-8 lg:gap-16 my-4">
                    <div style="width: 60%;">
                        <div class="grid gap-4">
                            <?php
                            $args = [
                                'post_type' => 'sakolawp-lesson',
                                'posts_per_page' => -1,
                                'meta_query' => [
                                    ['key' => 'sakolawp_subject_id', 'value' => (string)get_the_ID(), 'compare' => '=']
                                ]
                            ];
                            $lessons = get_posts($args);

                            if (count($lessons) > 0) {
                            ?>
                                <h3 class="mb-0">Curriculum</h3>
                                <div class="grid gap-0">
                                    <?php
                                    foreach ($lessons as $key => $lesson) {
                                    ?>
                                        <div class="shadow-sm border hover:shadow-md rounded-md p-4 md:p-8 flex gap-4 justify-between items-center">

                                            <div>
                                                <div class="uppercase text-gray-600 text-sm">
                                                    Lesson <?= $key + 1 ?> •
                                                    <span class="uppercase text-gray-600 text-sm">5 min </span>
                                                </div>
                                                <div class="font-medium"> <?= get_the_title($lesson->ID); ?></div>

                                            </div>
                                            <div>
                                                <div class="uppercase text-gray-600 text-sm"><i class="pi pi-caret-right"></i> </div>
                                            </div>

                                        </div>
                                    <?php
                                    }
                                    ?>
                                </div>
                            <?php
                            } else {
                            ?>
                                No Lessons Yet.
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="shadow" style="width: 40%;">
                        <!-- Display post featured image -->
                        <?php the_post_thumbnail(); ?>

                        <div class=" p-4 md:p-8">
                            <h4>About this course</h4>
                            <div class="text-xl grid gap-2">
                                <div class="flex gap-4 items-center"><i class="font-bold pi pi-tag"></i> <span>Free</span></div>
                                <div class="flex gap-4 items-center"><i class="font-bold pi pi-list"></i> <span><?= (string)count($lessons) . (count($lessons) >= 2 ? ' Lessons' : ' Lesson') ?></span></div>
                                <div class="flex gap-4 items-center"><i class="font-bold pi pi-list-check"></i> <span>23 Assessments</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        <?php
        endwhile;
        ?>
    </main>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
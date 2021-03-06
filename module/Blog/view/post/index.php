<h2>Blog Posts</h2>
<?php if (count($entries)): ?>
    <ol>
        <?php foreach ($entries as $entry): ?>
        <li>
            <h3>
                <a href="<?php $o->esc($h->url->get('blog.show', array('slug' => $entry->slug))); ?>"><?php $o->esc($entry->title); ?></a>
            </h3>
            <p>
                <?php $o->esc(strip_tags($entry->text)); ?>
            </p>
            <p>
                Tags: (<?php echo count($entry->getTags()); ?>)
                <?php foreach ($entry->getTags() as $tag): ?>
                <span><?php $o->esc($tag->title); ?></span>
                <?php endforeach; ?>
            </p>
        </li>
        <?php endforeach; ?>
    </ol>
    <?php echo $pager->getHtml(); ?>
<?php else: ?>
    <p><?php echo 'No posts available :('; ?></p>
<?php endif; ?>
<?php
queue_js_file('items-browse');
// $pageTitle = __('Browse Items') . ' ' . __('(%s total)', $total_results);
$pageTitle = $total_results . ' ' . __('Browse Items');
echo head(
    array(
        'title' => $pageTitle,
        'bodyclass' => 'items browse'
    )
);
echo flash();
echo item_search_filters();
$omekaUsers = AdminThemeHelper::getAllUsers();
?>

<?php if ($total_results): ?>
    <?php echo pagination_links(); ?>

    <form action="<?php echo html_escape(url('items/batch-edit')); ?>" method="post" accept-charset="utf-8">
        <?php if (is_allowed('Items', 'add')): ?>
        <a href="<?php echo html_escape(url('items/add')); ?>" class="add button small green"><?php echo __('Add an Item'); ?></a>
        <?php endif; ?>
        <?php echo link_to_item_search(__('Search Items'), array('class' => 'small blue advanced-search-link button')); ?>
        <?php echo common('quick-filters', array(), 'items'); ?>

        <div class="table-actions batch-edit-option">
            <?php if (is_allowed('Items', 'edit') || is_allowed('Items', 'delete')): ?>
                <button class="batch-all-toggle" type="button" data-records-count="<?php echo $total_results; ?>"><?php echo __('Select all %s results', $total_results); ?></button>
                <div class="selected"><span class="count">0</span> <?php echo __('items selected'); ?></div>
                <input type="hidden" name="batch-all" value="1" id="batch-all" disabled>
                <?php echo $this->formHidden('params', json_encode(Zend_Controller_Front::getInstance()->getRequest()->getParams())); ?>
                <?php if (is_allowed('Items', 'delete')): ?>
                <input type="submit" class="small batch-action button" name="submit-batch-delete" value="<?php echo __('Delete'); ?>">
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <table id="items">
        <thead>
            <tr>
                <?php if (is_allowed('Items', 'edit')): ?>
                <th class="batch-edit-heading"><?php echo __('Select all rows'); ?></th>
                <?php endif; ?>
                <?php
                $browseHeadings[__('Title')] = 'Dublin Core,Title';
                $browseHeadings[__('erstellt von')] = 'owner_id';
                $browseHeadings[__('Date Added')] = 'added';
                $browseHeadings[__('geändert am')] = 'modified';
                echo browse_sort_links($browseHeadings, array('link_tag' => 'th scope="col"', 'list_tag' => ''));
                ?>
            </tr>
        </thead>
        <tbody>
            <?php $key = 0; ?>
            <?php foreach (loop('Item') as $item): ?>
            <tr class="item <?php if(++$key%2==1) echo 'odd'; else echo 'even'; ?>">
                <?php $id = metadata('item', 'id'); ?>

                <?php if (is_allowed($item, 'edit') || is_allowed($item, 'tag')): ?>
                <td class="batch-edit-check">
                    <input type="checkbox" name="items[]" value="<?php echo $id; ?>"
                        aria-label="<?php echo html_escape(
                            __('Select item "%s"',
                                metadata('item', 'display_title', array('no_escape' => true))
                            )
                        ); ?>"
                    >
                </td>
                <?php endif; ?>

                <?php if ($item->featured): ?>
                <td class="item-info featured">
                <?php else: ?>
                <td class="item-info">
                <?php endif; ?>

                    <?php if (metadata('item', 'has files')): ?>
                    <?php echo link_to_item(item_image('square_thumbnail', array(), 0, $item), array('class' => 'item-thumbnail'), 'show', $item); ?>
                    <?php endif; ?>

                    <span class="title">
                    <?php echo link_to_item(); ?>

                    <?php if(!$item->public): ?>
                    <?php echo __('(Private)'); ?>
                    <?php endif; ?>
                    </span>
                    <ul class="action-links group">
                        <?php if (is_allowed($item, 'edit')): ?>
                        <li><?php echo link_to_item(__('Edit'), array(), 'edit'); ?></li>
                        <?php endif; ?>

                        <?php if (is_allowed($item, 'delete')): ?>
                        <li><?php echo link_to_item(__('Delete'), array('class' => 'delete-confirm'), 'delete-confirm'); ?></li>
                        <?php endif; ?>
                        <?php fire_plugin_hook('admin_items_browse_simple_each', array('item' => $item, 'view' => $this)); ?>
                    </ul>

                    <?php $exhibitPages = ExhibitDdbHelper::findItemInExhibitPage($item->id); ?>
                    <?php if (is_array($exhibitPages) && !empty($exhibitPages)): ?>
                    <div class="details">
                            <p style="margin: 0.2em 0 0 0;">Objekt in Ausstellungsseiten:</p>
                            <ul style="margin:0;padding: 0 0 0 16px;">
                            <?php foreach ($exhibitPages as $page): ?>
                                <?php echo ExhibitDdbHelper::getEditPageEntry($page); ?>
                            <?php endforeach; ?>
                            </ul>
                    </div>
                    <?php endif; ?>

                    <!--
                        <?php // echo snippet_by_word_count(metadata('item', array('Dublin Core', 'Description')), 40); ?>
                        <p>
                            <strong><?php // echo __('Collection'); ?>:</strong>
                            <?php // echo link_to_collection_for_item(); ?>
                        </p>
                        <p>
                            <strong><?php // echo __('Tags'); ?>:</strong>
                            <?php // if ($tags = tag_string('items')) // echo $tags; else echo __('No Tags'); ?>
                        </p>
                        <?php // fire_plugin_hook('admin_items_browse_detailed_each', array('item' => $item, 'view' => $this)); ?>
                    //-->
                </td>
                <!-- <td><?php // echo strip_formatting(metadata('item', array('Dublin Core', 'Creator'))); ?></td> -->

                <td><?php
                $currentUser = AdminThemeHelper::findOwnerInUsers($item->owner_id, $omekaUsers);
                if (false !== $currentUser && is_object($currentUser)) {
                    echo $currentUser->name;
                }
                ?></td>
                <td><?php echo format_date(metadata('item', 'added')); ?></td>
                <td><?php echo format_date(metadata('item', 'modified')); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
        <div class="table-actions batch-edit-option">
            <?php if (is_allowed('Items', 'edit') || is_allowed('Items', 'delete')): ?>
                <button class="batch-all-toggle" type="button" data-records-count="<?php echo $total_results; ?>"><?php echo __('Select all %s results', $total_results); ?></button>
                <div class="selected"><span class="count">0</span> <?php echo __('items selected'); ?></div>
                <?php if (is_allowed('Items', 'delete')): ?>
                <input type="submit" class="small batch-action button" name="submit-batch-delete" value="<?php echo __('Delete'); ?>">
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php echo pagination_links(); ?>
        <?php if (is_allowed('Items', 'add')): ?>
        <a href="<?php echo html_escape(url('items/add')); ?>" class="add button small green"><?php echo __('Add an Item'); ?></a>
        <?php endif; ?>
        <?php echo link_to_item_search(__('Search Items'), array('class' => 'small blue advanced-search-link button')); ?>
        <?php echo common('quick-filters', array(), 'items'); ?>
    </form>


    <script type="text/javascript">
    // Omeka.addReadyCallback(Omeka.ItemsBrowse.setupDetails, [
    //     <?php echo js_escape(__('Details')); ?>,
    //     <?php echo js_escape(__('Show Details')); ?>,
    //     <?php echo js_escape(__('Hide Details')); ?>
    // ]);
    Omeka.addReadyCallback(Omeka.ItemsBrowse.setupBatchEdit);
    </script>

<?php else: ?>
    <?php $total_items = total_records('Item'); ?>
    <?php if ($total_items === 0): ?>
        <h2><?php echo __('You have no items.'); ?></h2>
        <?php if(is_allowed('Items', 'add')): ?>
            <p><?php echo __('Get started by adding your first item.'); ?></p>
            <a href="<?php echo html_escape(url('items/add')); ?>" class="add big green button"><?php echo __('Add an Item'); ?></a>
        <?php endif; ?>
    <?php else: ?>
        <p>
            <?php echo __(plural('The query searched 1 item and returned no results.', 'The query searched %s items and returned no results.', $total_items), $total_items); ?>
            <?php echo __('Would you like to %s?', link_to_item_search(__('refine your search'))); ?>
        </p>
    <?php endif; ?>
<?php endif; ?>

<?php fire_plugin_hook('admin_items_browse', array('items' => $items, 'view' => $this)); ?>

<?php echo foot(); ?>

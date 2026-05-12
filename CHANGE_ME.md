# Change Me Checklist

Use this checklist after copying `vinny/sidebarblock_skeleton` to create a new sidebar block extension.

This skeleton ships with a birthdays block as the working example. When creating another block, replace the birthdays identifiers with your new block identifiers.

## Required Renames

1. Rename the extension folder:

```text
sidebarblock_skeleton -> sidebarblock_your_block
```

2. Rename PHP namespaces:

```text
vinny\sidebarblock_skeleton -> vinny\sidebarblock_your_block
```

3. Rename phpBB extension references:

```text
vinny/sidebarblock_skeleton -> vinny/sidebarblock_your_block
```

4. Rename service IDs:

```text
vinny.sidebarblock_skeleton.listener -> vinny.sidebarblock_your_block.listener
```

5. Rename the language file:

```text
language/en/sidebarblock_skeleton.php -> language/en/sidebarblock_your_block.php
```

6. Rename the language set in `event/listener.php`:

```php
'lang_set' => 'sidebarblock_your_block',
```

7. Rename the birthdays block keys:

```text
SIDEBAR_SKELETON_BIRTHDAYS -> SIDEBAR_YOUR_BLOCK
SIDEBAR_SKELETON_BIRTHDAYS_EMPTY -> SIDEBAR_YOUR_BLOCK_EMPTY
```

8. Rename template namespace references:

```text
@vinny_sidebarblock_skeleton/birthdays.html -> @vinny_sidebarblock_your_block/your_block.html
```

9. Rename template files:

```text
styles/all/template/birthdays.html -> styles/all/template/your_block.html
```

10. Rename template loop and switch variables:

```text
S_SIDEBARBLOCK_SKELETON_BIRTHDAYS -> S_SIDEBAR_YOUR_BLOCK
sidebarblock_skeleton_birthdays -> sidebar_your_block_items
```

11. Rename CSS classes:

```text
vinny-sidebarblock-skeleton-birthdays -> vinny-sidebarblock-your-block
```

12. Replace `render_birthdays()` with the new block renderer:

```php
protected function render_your_block()
{
	return true;
}
```

13. Update these files before testing:

```text
composer.json
ext.php
config/services.yml
event/listener.php
language/en/*.php
migrations/v100_initial.php
styles/all/template/*.html
styles/all/theme/stylesheet.css
README.md
```

14. Purge phpBB cache.

15. Enable the new extension in the ACP.

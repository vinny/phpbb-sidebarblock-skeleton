# Sidebar Block Skeleton

`vinny/sidebarblock_skeleton` is a reusable companion extension for [`vinny/sidebar`](https://github.com/vinny/phpbb-sidebar)

Use this package as a base when you want to create one small phpBB extension that adds one or more PHP-driven sidebar blocks. The parent Sidebar Manager extension owns the ACP interface, block ordering, side placement, enabled/disabled state, frontend block containers, and the custom render event. This skeleton only registers extra system blocks and fills those blocks with data from PHP.

## Requirements

- phpBB 3.3.0 or newer.
- PHP 7.2 or newer.
- [`vinny/sidebar`](https://github.com/vinny/phpbb-sidebar) installed and enabled before this extension is enabled.

If Sidebar Manager is not enabled, phpBB shows the localized dependency message from `SIDEBARBLOCK_SKELETON_REQUIRES_SIDEBAR` and does not activate this extension.

## What This Skeleton Includes

The included demonstration block is:

- `SIDEBAR_SKELETON_BIRTHDAYS`: displays today's birthdays using phpBB's native birthday rules.

This block exists as a working example. When creating a real block extension from this skeleton, rename the extension identifiers and replace the birthdays logic with the new block logic.

## How It Works

1. `migrations/v100_initial.php` inserts system block rows into Sidebar Manager's `vinny_sidebar_blocks` table.
2. Sidebar Manager renders active blocks during `core.page_header`.
3. For system blocks, Sidebar Manager dispatches the custom event `vinny.sidebar.render_system_block`.
4. `event/listener.php` checks whether the current block belongs to this extension.
5. The listener prepares template variables and assigns a template file from this extension.
6. Sidebar Manager renders the block using its normal sidebar layout.

The parent extension remains responsible for where the block appears. This skeleton decides what the block contains.

## File Structure

```text
vinny/sidebarblock_skeleton/
├── composer.json
├── CHANGE_ME.md
├── create-sidebar-block.ps1
├── ext.php
├── config/
│   └── services.yml
├── event/
│   └── listener.php
├── language/
│   └── en/
│       └── sidebarblock_skeleton.php
├── migrations/
│   └── v100_initial.php
├── styles/
│   └── all/
│       ├── template/
│       │   └── birthdays.html
│       └── theme/
│           └── stylesheet.css
└── README.md
```

## Important Files

### `composer.json`

Defines the phpBB extension name and display name.

When copying this skeleton, change:

- `name`;
- `description`;
- `homepage`;
- `extra.display-name`.

The `name` must match the extension path. For this skeleton, `vinny/sidebarblock_skeleton` lives in `ext/vinny/sidebarblock_skeleton`.

### `ext.php`

Handles extension lifecycle behavior.

- `is_enableable()` prevents activation if Sidebar Manager is not enabled.
- `enable_step()` re-enables this extension's block rows when the extension is enabled again.
- `disable_step()` disables this extension's block rows when the extension is disabled but data is kept.
- `set_sidebar_blocks_enabled()` updates the block rows owned by this extension.

When adding or replacing blocks, update the `$block_names` array inside `set_sidebar_blocks_enabled()`.

### `config/services.yml`

Registers the event listener in phpBB's service container.

When copying this skeleton, change the service ID and class namespace:

```yml
services:
    vinny.sidebarblock_skeleton.listener:
        class: vinny\sidebarblock_skeleton\event\listener
```

### `migrations/v100_initial.php`

Registers and removes this extension's system blocks.

- `insert_sidebar_blocks()` inserts rows into Sidebar Manager's table.
- `remove_sidebar_blocks()` deletes rows when the administrator disables the extension and chooses to delete data.
- `get_sidebar_blocks()` is the central list of blocks owned by this extension.

When adding a new dynamic block, add an entry to `get_sidebar_blocks()`.

### `event/listener.php`

Contains the runtime logic for dynamic blocks.

- `load_language_on_setup()` loads language keys.
- `on_sidebar_render_system_block()` receives the parent event and maps block names to templates.
- `render_birthdays()` prepares the demonstration birthdays block data.

When replacing the demonstration block, replace the birthday branch and create a render method for the new block.

### `styles/all/template/*.html`

Contains block-specific templates. These templates are not full sidebar containers. They only render the content inside a block that Sidebar Manager already created.

### `styles/all/theme/stylesheet.css`

Contains block-specific CSS for this extension. Shared sidebar layout should stay in the parent Sidebar Manager extension.

## Creating a New Block Extension From This Skeleton

Example target: a groups block extension at `ext/vinny/sidebarblock_groups`.

For a short copy checklist, see [`CHANGE_ME.md`](CHANGE_ME.md).

## Fast Generator

This skeleton includes a PowerShell helper for the repetitive rename work:

Open a terminal inside the skeleton folder:

```text
ext/vinny/sidebarblock_skeleton
```

Then run the generator with your new block values:

```powershell
pwsh ./create-sidebar-block.ps1 -Name groups -BlockKey SIDEBAR_GROUPS_LIST -Title "Sidebar Groups Block"
```

Edit these command values for your new extension:

| Parameter | What it controls | Example |
| --- | --- | --- |
| `-Name` | Folder suffix, PHP namespace suffix, template filename, render method suffix, and CSS suffix. Use lowercase letters, numbers, and underscores only. | `groups` |
| `-BlockKey` | Main language key and block identifier stored in Sidebar Manager's block table. Use uppercase letters, numbers, and underscores only. | `SIDEBAR_GROUPS_LIST` |
| `-Title` | Human-readable extension display name used by generated files. | `Sidebar Groups Block` |

The script copies this skeleton to:

```text
ext/vinny/sidebarblock_groups
```

It also renames common identifiers such as namespace, composer name, service ID, language file, block key, template file, template variables, and CSS class.

After generation, edit the generated extension, not the skeleton:

```text
ext/vinny/sidebarblock_groups
```

The script does not write the real data query for the new block. You still need to edit:

- `event/listener.php`: replace the example birthday query and permissions with the new block logic.
- `styles/all/template/groups.html`: adjust the HTML output for the new block.
- `language/en/sidebarblock_groups.php`: adjust visible text and empty-state messages.
- `migrations/v100_initial.php`: confirm `sidebar_side`, `block_order`, and `block_enabled` defaults.
- `composer.json`: confirm description, homepage, display name, and version.
- `styles/all/theme/stylesheet.css`: adjust or remove block-specific CSS.

After editing, purge the phpBB cache and enable the generated extension in the ACP.

## Replacement Table

Use this table when copying manually or reviewing generated files.

| Skeleton value | Example replacement |
| --- | --- |
| `sidebarblock_skeleton` | `sidebarblock_groups` |
| `sidebarblock-skeleton` | `sidebarblock-groups` |
| `Sidebar Block Skeleton` | `Sidebar Groups Block` |
| `vinny/sidebarblock_skeleton` | `vinny/sidebarblock_groups` |
| `vinny\sidebarblock_skeleton` | `vinny\sidebarblock_groups` |
| `vinny.sidebarblock_skeleton.listener` | `vinny.sidebarblock_groups.listener` |
| `@vinny_sidebarblock_skeleton/birthdays.html` | `@vinny_sidebarblock_groups/groups.html` |
| `SIDEBAR_SKELETON_BIRTHDAYS` | `SIDEBAR_GROUPS_LIST` |
| `SIDEBAR_SKELETON_BIRTHDAYS_EMPTY` | `SIDEBAR_GROUPS_LIST_EMPTY` |
| `S_SIDEBARBLOCK_SKELETON_BIRTHDAYS` | `S_SIDEBAR_GROUPS_LIST` |
| `sidebarblock_skeleton_birthdays` | `sidebar_groups_items` |
| `birthdays.html` | `groups.html` |
| `render_birthdays()` | `render_groups()` |
| `vinny-sidebarblock-skeleton-birthdays` | `vinny-sidebarblock-groups` |

### 1. Copy the folder

Copy:

```text
ext/vinny/sidebarblock_skeleton
```

To:

```text
ext/vinny/sidebarblock_groups
```

### 2. Rename PHP namespaces

Replace:

```php
vinny\sidebarblock_skeleton
```

With:

```php
vinny\sidebarblock_groups
```

Apply this to `ext.php`, `event/listener.php`, and `migrations/v100_initial.php`.

### 3. Rename phpBB extension references

Replace:

```text
vinny/sidebarblock_skeleton
```

With:

```text
vinny/sidebarblock_groups
```

Apply this to `composer.json`, `ext.php`, and `event/listener.php`.

### 4. Rename services

In `config/services.yml`, replace:

```yml
vinny.sidebarblock_skeleton.listener
vinny\sidebarblock_skeleton\event\listener
```

With:

```yml
vinny.sidebarblock_groups.listener
vinny\sidebarblock_groups\event\listener
```

### 5. Rename the language file

Rename:

```text
language/en/sidebarblock_skeleton.php
```

To:

```text
language/en/sidebarblock_groups.php
```

Then update `event/listener.php`:

```php
'lang_set' => 'sidebarblock_groups',
```

### 6. Rename block language keys

For a groups block, replace:

```php
SIDEBAR_SKELETON_BIRTHDAYS
SIDEBAR_SKELETON_BIRTHDAYS_EMPTY
```

With:

```php
SIDEBAR_GROUPS_LIST
SIDEBAR_GROUPS_LIST_EMPTY
```

Use the same block key everywhere:

- `language/en/sidebarblock_groups.php`;
- `migrations/v100_initial.php`;
- `ext.php`;
- `event/listener.php`;
- template files.

### 7. Rename the template namespace

Replace:

```php
'@vinny_sidebarblock_skeleton/birthdays.html'
```

With:

```php
'@vinny_sidebarblock_groups/groups.html'
```

Then rename:

```text
styles/all/template/birthdays.html
```

To:

```text
styles/all/template/groups.html
```

### 8. Replace the render method

Remove `render_birthdays()` and create a method for the new block, for example:

```php
protected function render_groups()
{
    // Query and permission logic goes here.
    // Assign template variables here.
    // Return false only when the whole block should be hidden.
    return true;
}
```

In `on_sidebar_render_system_block()`, call the new method:

```php
if ($row['block_name'] === 'SIDEBAR_GROUPS_LIST')
{
    if (!$this->render_groups())
    {
        $block_data['S_DISPLAY'] = false;
    }
    else
    {
        $block_data['TEMPLATE_FILE'] = '@vinny_sidebarblock_groups/groups.html';
    }
}
```

## Included Birthdays Block Behavior

The demonstration birthdays block mirrors phpBB's native index birthday logic.

It respects:

- `load_birthdays`;
- `allow_birthdays`;
- profile/user administration permissions used by phpBB's birthday list;
- banned-user filtering;
- normal/founder user filtering;
- phpBB's leap-year behavior for February 29 birthdays;
- `core.index_modify_birthdays_sql`;
- `core.index_modify_birthdays_list`.

If birthdays are disabled by phpBB settings or permissions, the block is hidden. If birthdays are enabled but nobody has a birthday today, the block remains visible and shows `SIDEBAR_SKELETON_BIRTHDAYS_EMPTY`.

## Security Notes

- Validate and sanitize user input before assigning it to templates.
- Use phpBB helpers such as `get_username_string()`, `append_sid()`, `censor_text()`, and database escaping methods where appropriate.
- Do not output raw request data in templates.
- Do not query private forum data unless the current user has permission to view it.
- For JavaScript strings, use phpBB's JavaScript-safe escaping, such as `{LA_LANGUAGE_KEY}` in phpBB templates.
- Keep block templates focused on output only; database and permission logic should stay in PHP.

## Cache

After changing services, templates, language files, or migrations, purge the phpBB cache before testing.

Service changes in `config/services.yml` require cache purge because phpBB compiles the dependency injection container.

## Requesting New Blocks

If you need a new ready-to-use sidebar block, open an issue in the project repository:

[Request a block](https://github.com/vinny/phpbb-sidebarblock-skeleton/issues)

When opening the issue, include:

- the block name;
- what data it should display;
- whether it depends on phpBB core data or another extension;
- any permission rules the block should respect;
- the expected empty state when there is no data to display;
- screenshots or examples, if available.

## License

[GNU General Public License v2](license.txt)

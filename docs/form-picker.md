# Form picker

`FileExplorerPicker` opens a modal with the embedded explorer.

![Form picker](../images/form-picker-modal.svg)

```php
FileExplorerPicker::make('media_ids')
    ->scopeKey('form.'.$record->id)
    ->rootFolderId($record->folder_id)
    ->multiple()
    ->label('Attachments');
```

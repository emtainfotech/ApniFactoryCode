<?php
$file = 'resources/views/product/list.blade.php';
$content = file_get_contents($file);

// Replace empty ms-auto with action buttons
$oldHeader = '<div class="ms-auto">';
$newHeader = '<div class="ms-auto d-flex gap-2">
						<a href="{{ url(\'seller/product/add\') }}" class="btn btn-primary"><i class="fa fa-plus me-1"></i> Add Product</a>
						<a href="{{ route(\'seller.product.bulk-upload\') }}" class="btn btn-success"><i class="fa fa-file-excel me-1"></i> Bulk Upload CSV</a>
					</div>';

// Also add success flash message and pagination links
$oldErrorDiv = '<div class="mb-3">';
$newErrorDiv = '<div class="mb-3">
                                @if(session(\'success\'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="fa fa-check-circle me-1"></i> {{ session(\'success\') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif';

$oldTableEnd = '</table>
                                        </div>';
$newTableEnd = '</table>
                                        </div>
                                        <div class="mt-3 d-flex justify-content-end">
                                            {{ $list->links() }}
                                        </div>';

$normalizedContent = str_replace("\r\n", "\n", $content);

if (strpos($normalizedContent, '<div class="ms-auto">') !== false) {
    // Replace the empty div block
    $normalizedContent = preg_replace('/<div class="ms-auto">\s*<\/div>/', $newHeader, $normalizedContent, 1);
    echo "Header buttons updated!\n";
}

if (strpos($normalizedContent, '<div class="mb-3">') !== false) {
    $normalizedContent = str_replace('<div class="mb-3">', $newErrorDiv, $normalizedContent);
    echo "Flash message updated!\n";
}

$normalizedOldTableEnd = str_replace("\r\n", "\n", $oldTableEnd);
$normalizedNewTableEnd = str_replace("\r\n", "\n", $newTableEnd);
if (strpos($normalizedContent, $normalizedOldTableEnd) !== false) {
    $normalizedContent = str_replace($normalizedOldTableEnd, $normalizedNewTableEnd, $normalizedContent);
    echo "Pagination links updated!\n";
}

file_put_contents($file, $normalizedContent);
echo "Done.\n";


#!/bin/bash

# Define the directory to start from
start_dir="."


# Use find to get all .tpl files and loop over them
find "$start_dir" -name '*.tpl' -print0 | while IFS= read -r -d '' file
do

    # Use perl to replace the Bootstrap 3 classes with Bootstrap 4 classes
    # replace col-lg-* classes with col-xl-*
    perl -i -pe 's/\bcol-lg-([0-9]+)\b/col-xl-$1/g' "$file"
    # replace col-md-* classes with col-lg-*
    perl -i -pe 's/\bcol-md-([0-9]+)\b/col-lg-$1/g' "$file"
    # replace col-sm-* classes with col-md-*
    perl -i -pe 's/\bcol-sm-([0-9]+)\b/col-md-$1/g' "$file"
    # replace col-xs-* classes with col-*
    perl -i -pe 's/\bcol-xs-([0-9]+)\b/col-$1/g' "$file"

    #  offset classes: example: col-lg-offset-* to offset-xl-*
    #  also change mediaquery names like for columns
    perl -i -pe 's/\bcol-lg-offset-([0-9]+)\b/offset-xl-$1/g' "$file"
    perl -i -pe 's/\bcol-md-offset-([0-9]+)\b/offset-lg-$1/g' "$file"
    perl -i -pe 's/\bcol-sm-offset-([0-9]+)\b/offset-md-$1/g' "$file"
    perl -i -pe 's/\bcol-xs-offset-([0-9]+)\b/offset-$1/g' "$file"


    # .pull-left to float-left
    perl -i -pe 's/(?<!name=)\bpull-left\b/float-left/g' "$file"
    # .pull-right to float-right
    perl -i -pe 's/(?<!name=)\bpull-right\b/float-right/g' "$file"



    # Bootstrap 3 to Bootstrap 4 utility class changes


    # Replace `.hidden` with `.d-none` - which Bootstrap 4 uses to hide elements
    perl -i -pe 's/(class=".*?)(?<!-)(\bhidden\b)(?!-)(.*?")/$1 . ($2 eq "hidden" ? "d-none" : $2) . $3/eg' "$file"
    
    # # Info: Utility classes with media queries are too complicated
    # # replace manually

    # # all combinations of hidden-* classes
    # perl -i -0777 -pe 's/(class="[^"]*?)(?<!-)\bhidden-xs\b(?!-)([^"]*")/$1d-none d-sm-block$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-sm\b(?!-)([^"]*")/$1d-none d-md-block d-sm-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-md\b(?!-)([^"]*")/$1d-none d-lg-block d-md-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-lg\b(?!-)([^"]*")/$1d-none d-xl-block d-lg-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-xs hidden-sm\b(?!-)([^"]*")/$1d-none d-sm-block d-none d-md-block d-sm-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-xs hidden-md\b(?!-)([^"]*")/$1d-none d-sm-block d-none d-lg-block d-md-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-xs hidden-lg\b(?!-)([^"]*")/$1d-none d-sm-block d-none d-xl-block d-lg-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-sm hidden-md\b(?!-)([^"]*")/$1d-none d-md-block d-sm-none d-none d-lg-block d-md-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-sm hidden-lg\b(?!-)([^"]*")/$1d-none d-md-block d-sm-none d-none d-xl-block d-lg-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-md hidden-lg\b(?!-)([^"]*")/$1d-none d-lg-block d-md-none d-none d-xl-block d-lg-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-xs hidden-sm hidden-md\b(?!-)([^"]*")/$1d-none d-sm-block d-none d-md-block d-sm-none d-none d-lg-block d-md-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-xs hidden-sm hidden-lg\b(?!-)([^"]*")/$1d-none d-sm-block d-none d-md-block d-sm-none d-none d-xl-block d-lg-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-xs hidden-md hidden-lg\b(?!-)([^"]*")/$1d-none d-sm-block d-none d-lg-block d-md-none d-none d-xl-block d-lg-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-sm hidden-md hidden-lg\b(?!-)([^"]*")/$1d-none d-md-block d-sm-none d-none d-lg-block d-md-none d-none d-xl-block d-lg-none$2/g;
    # s/(class="[^"]*?)(?<!-)\bhidden-xs hidden-sm hidden-md hidden-lg\b(?!-)([^"]*")/$1d-none d-sm-block d-none d-md-block d-sm-none d-none d-lg-block d-md-none d-none d-xl-block d-lg-none$2/g;
    # ' "$file"

    # perl -i -0777 -pe '
    #     s/\bvisible-xs-block\b/d-block d-sm-none/g;
    #     s/\bvisible-sm-block\b/d-none d-sm-block d-md-none/g;
    #     s/\bvisible-md-block\b/d-none d-md-block d-lg-none/g;
    #     s/\bvisible-lg-block\b/d-none d-lg-block d-xl-none/g;

    #     s/\bvisible-xs-inline\b/d-inline d-sm-none/g;
    #     s/\bvisible-sm-inline\b/d-none d-sm-inline d-md-none/g;
    #     s/\bvisible-md-inline\b/d-none d-md-inline d-lg-none/g;
    #     s/\bvisible-lg-inline\b/d-none d-lg-inline d-xl-none/g;

    #     s/\bvisible-xs-inline-block\b/d-inline-block d-sm-none/g;
    #     s/\bvisible-sm-inline-block\b/d-none d-sm-inline-block d-md-none/g;
    #     s/\bvisible-md-inline-block\b/d-none d-md-inline-block d-lg-none/g;
    #     s/\bvisible-lg-inline-block\b/d-none d-lg-inline-block d-xl-none/g;

    #     s/\bvisible-xs\b/d-block d-sm-none/g;
    #     s/\bvisible-sm\b/d-none d-sm-block d-md-none/g;
    #     s/\bvisible-md\b/d-none d-md-block d-lg-none/g;
    #     s/\bvisible-lg\b/d-none d-lg-block d-xl-none/g;
    # ' "$file"
    perl -i -pe 's/\bhidden-print\b/d-print-none/g' "$file"
    perl -i -pe 's/\binvisible\b/visibility-hidden/g' "$file"

    # .nav and .nav-* classes changes
    perl -i -pe 's/\bnav-stacked\b/flex-column/g' "$file"
    perl -i -pe 's/\bnav-tabs\b/nav nav-tabs/g' "$file"
    perl -i -pe 's/\bnav-pills\b/nav nav-pills/g' "$file"

    # Navbar related changes
    perl -i -pe 's/\bnavbar-default\b/navbar-light bg-light/g' "$file"
    perl -i -pe 's/\bnavbar-fixed-top\b/fixed-top/g' "$file"
    perl -i -pe 's/\bnavbar-fixed-bottom\b/fixed-bottom/g' "$file"



    # Dropdown related changes
    perl -i -pe 's/\bdropdown-menu\b/dropdown-menu/g' "$file"  # Ensure dropdown-menu class persists
    perl -i -pe 's/\bdropdown-header\b/dropdown-item-text/g' "$file"
    perl -i -pe 's/\bbtn-group-vertical\b/btn-group flex-column/g' "$file"
    perl -i -pe 's/\bdropdown-header\b/dropdown-item-text/g' "$file"
    perl -i -pe 's/\bbtn-group-vertical\b/btn-group flex-column/g' "$file"

    # Form related changes
    perl -i -pe 's/\bcontrol-label\b/col-form-label/g' "$file"
    perl -i -pe 's/\binput-lg\b/form-control-lg/g' "$file"
    perl -i -pe 's/\binput-sm\b/form-control-sm/g' "$file"
    perl -i -pe 's/\bhelp-block\b/form-text/g' "$file"


    # panal to card
    perl -i -pe 's/(?<!name=)(?<![-.])\bpanel\b(?!-)/card/g' "$file"
    # remove panel-default (without quotes around classname)
    perl -i -pe 's/(?<!name=)\bpanel-default\b//g' "$file"
    # panel-heading to card-header
    perl -i -pe 's/(?<!name=)\bpanel-heading\b/card-header/g' "$file"
    # panel-title to card-title
    perl -i -pe 's/(?<!name=)\bpanel-title\b//g' "$file"
    # panel-body to card-body
    perl -i -pe 's/(?<!name=)\bpanel-body\b/card-body/g' "$file"
    # panel-footer to card-footer
    perl -i -pe 's/(?<!name=)\bpanel-footer\b/card-footer/g' "$file"
    
    # 

    perl -i -pe 's/(?<!name=)\bwell\b(?!-)/card card-body/g' "$file"
    perl -i -pe 's/(?<!name=)(?<![-.])\bthumbnail\b(?!-)/card card-body/g' "$file"


    # btn-default to btn-secondary
    perl -i -pe 's/(?<!name=)\bbtn-default\b/btn-secondary/g' "$file"
    perl -i -pe 's/(?<!name=)\bimg-responsive\b/img-fluid/g' "$file"
    perl -i -pe 's/(?<!name=)\bimg-circle\b/rounded-circle/g' "$file"
    perl -i -pe 's/(?<!name=)\bimg-rounded\b/rounded/g' "$file"


    perl -i -pe "s/(?<!name=)(?<![-\.'])\bbadge\b/badge badge-pill/g" "$file"
    perl -i -pe "s/(?<!name=)(?<![-\.<'])(?<!<\/)(?<!\[\\\")\blabel\b(?!-)/badge/g" "$file"

done
core = 7.x
api = 2

; Projects
projects[ding_page][type] = "module"
projects[ding_page][download][type] = "git"
projects[ding_page][download][url] = "git@github.com:ding2tal/ding_page.git"
projects[ding_page][download][branch] = "development"

projects[cache_actions][subdir] = "contrib"
projects[cache_actions][version] = "2.0-alpha5"

projects[ctools][subdir] = "contrib"
projects[ctools][version] = "1.4"

projects[entity][subdir] = "contrib"
projects[entity][version] = "1.3"

projects[features][subdir] = "contrib"
projects[features][version] = "2.0-beta2"

projects[features_extra][subdir] = "contrib"
projects[features_extra][version] = "1.0-beta1"

projects[media][type] = "module"
projects[media][subdir] = "contrib"
projects[media][download][type] = "git"
projects[media][download][url] = "http://git.drupal.org/project/media.git"
projects[media][download][revision] = "c3cda2b"

projects[file_entity][subdir] = "contrib"
projects[file_entity][version] = "2.0-alpha2"

projects[panels][subdir] = "contrib"
projects[panels][version] = "3.3"
projects[panels][patch][0] = "http://drupal.org/files/1649046-form-wrapper-is-where-form-is-now.patch"

projects[rules][subdir] = "contrib"
projects[rules][version] = "2.3"

projects[similarterms][subdir] = "contrib"
projects[similarterms][version] = "2.3"

projects[strongarm][subdir] = "contrib"
projects[strongarm][version] = "2.0"

projects[views][subdir] = "contrib"
projects[views][version] = "3.7"

projects[workbench][subdir] = "contrib"
projects[workbench][version] = "1.2"

projects[wysiwyg][subdir] = "contrib"
projects[wysiwyg][version] = "2.2"

projects[media_vimeo][subdir] = "contrib"
projects[media_vimeo][version] = "2.0-rc1"

projects[media_youtube][type] = "module"
projects[media_youtube][subdir] = "contrib"
projects[media_youtube][download][type] = "git"
projects[media_youtube][download][url] = "http://git.drupal.org/project/media_youtube.git"
projects[media_youtube][download][revision] = "ca46aba"
projects[media_youtube][patch][] = "http://drupal.org/files/issues/provide-access-wrapper-1823376-6.patch"

projects[node_clone][subdir] = "contrib"
projects[node_clone][version] = "1.0-rc2"

projects[image_resize_filter][subdir] = "contrib"
projects[image_resize_filter][version] = "1.14"

; Libraries
libraries[ckeditor][download][type] = "get"
libraries[ckeditor][download][url] = "http://download.cksource.com/CKEditor/CKEditor/CKEditor%203.6.6.1/ckeditor_3.6.6.1.zip"
libraries[ckeditor][directory_name] = "ckeditor"
libraries[ckeditor][destination] = "libraries"

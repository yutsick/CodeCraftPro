<?php
/*
Plugin Name: CodeCraftPro
Description: Тестове завдання для CodeCraftPro
Author: Taras Yurchyshyn
Version: 1.0
*/

//Реєструємо новий тип поста
add_action('init', 'create_contact_form');
function create_contact_form()
{
    register_post_type('test_contacts_forms', array(
            'labels' => array(
                'name' => 'Contacts', // основное название для типа записи
                'singular_name' => 'Contact', // название для одной записи этого типа
                'add_new' => 'Add contact', // для добавления новой записи
                'add_new_item' => 'Add new contact', // заголовка у вновь создаваемой записи в админ-панели.
                'edit_item' => 'Edit contact', // для редактирования типа записи
                'new_item' => 'New contact', // текст новой записи
                'view_item' => 'View contact', // для просмотра записи этого типа.
                'search_items' => 'Seach contact', // для поиска по этим типам записи
                'not_found' => 'No contacts found', // если в результате поиска ничего не было найдено
                'not_found_in_trash' => 'No contacts in trash', // если не было найдено в корзине
                'parent_item_colon' => 'Parent contact', // для родительских типов. для древовидных типов
                'menu_name' => 'Contacts', // название меню
            ),
            'description' => 'Contacts form as test task for CodeCraftPro',
            'public' => true,
            'menu_position' => 15,
            'hierarchical' => false,
            'supports' => array('title'),
            'taxonomies' => array(''),
            'has_archive' => true,
        )
    );
}
//Виводимо метабокс з необхідними полями в адмінку

add_action('admin_init', 'contact_fields');
function contact_fields() //Додаємо метабокс
{
    add_meta_box('contact_form_fields',
        'Contact details',
        'contact_fields_display',
        'test_contacts_forms',
        'normal', 'high'
    );

}

function contact_fields_display($contacts_view) //ініціалізуємо поля в метабоксі
{
    $contact_name = esc_html(get_post_meta($contacts_view->ID, 'contact_name', TRUE));
    $contact_email = esc_html(get_post_meta($contacts_view->ID, 'contact_email', TRUE));
    $contact_phone = intval(get_post_meta($contacts_view->ID, 'contact_phone', TRUE));
?>
    <table>
        <tr>
            <td style="width: 100%">Contact Name</td>
            <td><input type="text" size="80" name="contact_name" value="<?php echo $contact_name; ?>"/></td>
        </tr>
        <tr>
            <td style="width: 150px">Contact e-mail</td>
            <td><input type="text" size="80" name="contact_email" value="<?php echo $contact_email; ?>"/></td>
        </tr>
        <tr>
            <td style="width: 150px">Contact Phone</td>
            <td><input type="text" size="80" name="contact_phone" value="<?php echo $contact_phone; ?>"/></td>
        </tr>
    </table>
    <?php
}

//Обновлюємо метаполя при редагуванні/додаванні пота з адмінки
add_action('save_post', 'add_contact_fields', 10, 2);
function add_contact_fields($contact_view_id, $contact_view)
{
    if ($contact_view->post_type == 'test_contacts_forms') {
        if (isset($_POST['contact_name']) && $_POST['contact_name'] != '') {
            update_post_meta($contact_view_id, 'contact_name', $_POST['contact_name']);
        }
        if (isset($_POST['contact_email']) && $_POST['contact_email'] != '') {
            update_post_meta($contact_view_id, 'contact_email', $_POST['contact_email']);
        }
        if (isset($_POST['contact_phone']) && $_POST['contact_phone'] != '') {
            update_post_meta($contact_view_id, 'contact_phone', $_POST['contact_phone']);
        }
    }
}

//Правимо вивід колонок в адмиінці
add_filter( 'manage_edit-test_contacts_forms_columns', 'contact_admin_change_columns' );
function contact_admin_change_columns($columns)
{
    $columns['contact_name'] = 'Name';
    $columns['contact_email'] = 'E-mail';
    $columns['contact_phone'] = 'Phone';
    unset( $columns['date']);//прибираємо колонку з датою
    return $columns;
}

add_action( 'manage_posts_custom_column', 'contact_admin_fill_columns' ); //заповняємо колонки даними з постів
function contact_admin_fill_columns($columns)
{
    if ('contact_name' == $columns ) {
        $contact_name = esc_html(get_post_meta(get_the_ID(), 'contact_name', TRUE));
        echo $contact_name;
    }
    elseif ('contact_email' == $columns ) {
        $contact_email = esc_html(get_post_meta(get_the_ID(), 'contact_email', TRUE));
        echo $contact_email;
    }
    elseif ('contact_phone' == $columns ) {
        $contact_phone = intval(get_post_meta(get_the_ID(), 'contact_phone', TRUE));
        echo $contact_phone;
    }
}

//додаємо шорткод для виведення форми [CCP_contact]
function CCP_contacts_form()
{
    ?>
<form method="post" id="CCP_contacts_form">
    <table>
        <tr>
            <td style="width: 100px">Contact Name</td>
            <td><input type="text" size="80" name="contact_name" value=""/></td>
        </tr>
        <tr>
            <td style="width: 150px">Contact e-mail</td>
            <td><input type="text" size="80" name="contact_email" value="<?php echo $contact_email; ?>"/></td>
        </tr>
        <tr>
            <td style="width: 150px">Contact Phone</td>
            <td><input type="text" size="80" name="contact_phone" value="<?php echo $contact_phone; ?>"/></td>
        </tr>
        <tr>
            <td style="width: 150px"></td>
            <td> <button type="submit">Send</button></td>
        </tr>
    </table>
    <input type="hidden" name="submitted" id="submitted" value="true" />
</form>
<?php
}
add_shortcode('CCP_contact', 'CCP_contacts_form');

//Обробка заповненої форми.
function add_new_contact ()
{
   if(isset($_POST['submitted'])) {
       if(trim($_POST['contact_name']) === '') {
           $hasError = true;
    } else {
        $name = trim($_POST['contact_name']);
    }
    if(trim($_POST['contact_email']) === '')  {
        $hasError = true;
    }
    else {
         $email = trim($_POST['contact_email']);
    }
    if(trim($_POST['contact_phone']) === '') {
        $hasError = true;
    } else {
        $phone = trim($_POST['contact_phone']);
        }

       if(!isset($hasError)) {
        $args = array(
            'post_title'    => $name,
            'post_content'  =>'',
            'post_status'   => 'publish',
            'post_type'     => 'test_contacts_forms',
            'meta_input'    => array( 'contact_name'=>$name, 'contact_email' => $email, 'contact_phone' => $phone)
        );
        $post_id = wp_insert_post( $args );
        }

    }

}
add_action('init', 'add_new_contact');
?>

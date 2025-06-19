<?
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'src/FormBuilder.php';
use CMG\FormBuilder\FormBuilder;
?><!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CodeMagster.com FormBuilder v.1</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    </head>
    <body>
        <div class="container mt-5">
            <h1>CodeMagster.com FormBuilder v.1</h1>
            <p class="lead">This is a simple form builder example.</p>
 <?


// Start session for CSRF
session_start();

$form = new FormBuilder();
$form->setMethod('POST')
     ->setAction('')
     ->setId('demo-form')
     ->setFormClass('p-4 border rounded')
     ->setFormAttributes(['data-test' => 'true','novalidate' => 'novalidate'])
     ->enableCsrf()
     //->enableDebug() // Output POST, FILES, errors
     ->onSubmit(function($data, $files) {
         echo "<div class='alert alert-success'>Form successfully submitted!</div>";
         echo "<pre>Sanitized Data:\n" . print_r($data, true) . "</pre>";
         echo "<pre>Files:\n" . print_r($files, true) . "</pre>";
     });

// Start form building
$form->startFieldset('Account Information')
     ->addField('user[name]', 'text', 'Full Name', [
         'required' => true
     ], [], 'Your full legal name')
     ->addField('user[email]', 'email', 'Email Address', [
         'required' => true,
         'validate' => 'email'
     ])
     ->endFieldset();

$form->startFieldset('Preferences')
     ->addField('newsletter', 'checkbox', 'Subscribe to newsletter', [
         'value' => 'yes',
         'checked' => true
     ])
     ->addField('interests[]', 'select', 'Interests', [
         'required' => true,
         'multiple' => true
     ], [
         'php' => 'PHP',
         'js' => 'JavaScript',
         'ai' => 'AI'
     ])
     ->endFieldset();

$form->startFieldset('Upload')
     ->addField('documents[cv]', 'file', 'Upload CV', ['required' => true])
     ->endFieldset();


$form->validate($_POST);
$form->render();
?>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-1q8d65b6d8f7e3c4f8a2b6c9d8f7e3c4f8a2b6c9d8f7e3c4f8a2b6c9d8f7e3c4f8a2b6c9d8f7e3c4f8a2b6c9d8f7e3c4f8a2b6c9d8f7e3c4f8a2b6c9d8f7e3c4f8a2b6c9d8f7e3c4f8a2b6c9d8f7e3c4f8a2b6c9d8f7e3c4f8a2b6c9d              " crossorigin="anonymous"></script>
    </body>
</html>
<?php
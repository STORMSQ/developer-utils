<?php
return [ 
    'uploadFile'=>[
            'path'=>[
                'temp'=>'temp',
                'images'=>'images',
                'documents'=>'documents',
                'FTPLocation'=>'uploads/ftp'
            ],
            'image'=>[
                'validFormat'=>['jpg','jpeg','png','gif','svg','ico','bmp']
            ],
            'document'=>[
                'validFormat'=>[]
            ],
       
    ]

];
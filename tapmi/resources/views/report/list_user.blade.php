<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report List Asset</title>
</head>
<body>
<div class="container">
    <div xclass="xtable-scroll ex1" xstyle="background-color: #FFF;overflow: auto;">

    <center><h1><u>REPORT ASSET</u></h1><h3>Per tanggal : <?php echo date('d/m/Y'); ?></h3></center>

    <?php 
        $l = "";
        $no = 1;

        if(!empty($report))
        {
            $l .= "<table border=1 cellspacing=0 cellpadding=5 class='table tabel-responsive table-bordered'>";
            $l .= "<tr>
                        <th>Auth Code</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th>Job Desc</th>
                        <th>User Role</th>
                        <th>Location</th>
                        <th>Ref Role</th>
                        <th>APK Version</th>
                        <th>APK Date</th>
                        <th>Status</th>
                    </tr>";
            

            foreach( $master_user as $q )
            {


                $l .= "<tr> 
                        <td>". $q['USER_AUTH_CODE']."</td>
                        <td>". $q['EMPLOYEE_NIK']."</td>
                        <td>". $q['FULLNAME']."</td>
                        <td>". $q['JOB']."</td>
                        <td>". $q['USER_ROLE']."</td>
                        <td>". $q['LOCATION_CODE']."</td>
                        <td>". $q['REF_ROLE']."</td>
                        <td>". $q['APK_VERSION']."</td>
                        <td>". $q['APK_DATE']."</td>
                        <td></td>
                </tr>
                ";
                

                $i++;
                $no++;
            }
            
            $l .= "</table>";
        }
        else
        {
            $l .= "Data not found!";
        }

        echo $l;

    ?>

    </div>
</div>
    
</body>
</html>
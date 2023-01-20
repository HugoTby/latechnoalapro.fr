<?php
	ini_set('display_errors', 1);
	error_reporting(E_ALL & ~E_NOTICE);
	$address="http://journal-lapro.fr/pedago/V2/";
	function connexion ()//se connecte à la DB
	{
		global $connexion;
		//$connexion =mysqli_connect("journalltrpedago.mysql.db", "journalltrpedago","Pedago1234");
		$connexion =mysqli_connect("fskkxwctechno.mysql.db", "fskkxwctechno","Sj6f84XHx3Dz");
		mysqli_select_db($connexion, "fskkxwctechno");
		mysqli_set_charset($connexion,"utf8");
	}
//-----------------------------------------------------------------------------------------------------------------------------------//
	function identification ($login,$password)
	{	
		global $connexion;
		$checkuser = mysqli_query($connexion, "SELECT `pseudo` FROM account WHERE pseudo = '$login'");
		if(mysqli_num_rows($checkuser) == 0)//Pseudo innexistant
		{
			$check=0;
		}
		else
		{
			$password=sha1($password);//encryptage du password
			$checkpassword = mysqli_query($connexion, "SELECT `password`,`user_level`,`user_avatar` FROM account WHERE pseudo = '$login'");
			while ($req=mysqli_fetch_array($checkpassword))
			{
				$mdpverif=$req[0];
				$user_level=$req[1];
				$user_avatar=$req[2];
			}
			if($password==$mdpverif)
			{
				$_SESSION['login']=$login;
				$_SESSION['user_level']=$user_level;
				$_SESSION['user_avatar']=$user_avatar;
				$check=1;
			}
			else
			{
				$check=2;
			}
		}
		return $check;
	}
//----------------------------------------------------------------------------------------------------------------------------------//	
	function navbar()
	{
		global $connexion;
		global $page;
		$select_categories= mysqli_query($connexion, "SELECT * FROM `categories` WHERE 1");
		if(mysqli_num_rows($select_categories) >= 1)
			{
			while ($tab=mysqli_fetch_array($select_categories))
				{
				$page=$tab[1];
				echo"
					<a href='index.php?p=cours&cl=$tab[1]'><div";
					if(isset($_GET['p'])&& $_GET['p']=='cour'&& $_GET['cl']==$tab[1])
					{
						echo" class='active' style='background-color:$tab[2]'";
					}
					else
					{
						echo" class='link' style='background-color:$tab[2]'";
						
					}
					 echo"><i class='fa fa-book' aria-hidden='true'></i> ".$tab[1]."</i>
					</div></a>";
				}	
			}	
	}
	function select_menu()
	{
		global $connexion;
		$select_categories= mysqli_query($connexion, "SELECT * FROM `categories` WHERE 1");
		if(mysqli_num_rows($select_categories) >= 1)
			{
			while ($tab=mysqli_fetch_array($select_categories))
				{
				echo"
					<a href='index.php?p=cours&cl=$tab[1]'>
						<div class='block_one' style='background-color:$tab[2]'>
							<div class='classe'>$tab[1]</div>
							<center><img src='$tab[3]' alt='logo' width='754' height='603' ><p></center>
							<div class='button'>
								<i class='fa fa-book' aria-hidden='true'></i> Cours
								<div class='clear'></div>
							</div>
						</div>
					</a>
							
							";
				}	
			}	
		
	}
//----------------------------------------------------------------------------------------------------------------------------------//	
	function add_categories($name,$color,$img_block,$title, $presentation)
	{
		global $connexion;
		$verif_classe = mysqli_query($connexion, "SELECT `classe` FROM categories WHERE classe = '$name'");
		if(mysqli_num_rows($verif_classe) == 1)
			{
				$check="false";
			}
		else
			{
				$dossier_img="../assets/img_categorie/";
				$fichier_img=basename($_FILES['img_block']['name']);
				
				$dossier_background="../assets/img_background/";
				$fichier_background=basename($_FILES['background']['name']);
				
				$liens=$dossier_img.$fichier_img;
				$liens_background=$dossier_background.$fichier_background;
				
				
				$var_requete_img="assets/img_categorie/".$fichier_img;
				$var_requete_background="assets/img_background/".$fichier_background;
				move_uploaded_file($_FILES['img_block']['tmp_name'], $liens);
				move_uploaded_file($_FILES['background']['tmp_name'], $liens_background);
				
				$requete="INSERT INTO `categories`(`classe`, `couleur`, `img`,`background`,`title`,`description`) VALUES ('$name','$color','$var_requete_img','$var_requete_background','$title','$presentation')";
			
				$enregistrement=mysqli_query($connexion, $requete)or die( mysqli_error() );;
				if(!$enregistrement)
				{
					$check="false";
				}
				else
				{
					$check="true";
				}
			}
		return $check;
	}
	function user_add ($pseudo,$password,$verif_password,$mail,$level,$nom,$prenom,$avatar)
	{
		global $connexion;
		global $check;
		$checklogin = mysqli_query($connexion, "SELECT `pseudo` FROM account WHERE pseudo = '$pseudo'");
		if(mysqli_num_rows($checklogin) == 1)
			{
				$check="error_account";
			}
		else
			{
				if($password==$verif_password)//On verifie que les deux MDP soient egaux
				{
					if(filter_var($mail, FILTER_VALIDATE_EMAIL))// vérification de la validité de l'email
					{
						if(empty($avatar))
						{
						$VarRequete="../assets/img_user/no-img.png";	
						}
						else
						{
						//------------Traitement de l'avatar---------//
							$dossier="../assets/img_user/";
							$fichier=basename($_FILES['avatar']['name']);
							$VarRequete=$dossier.$fichier;
							move_uploaded_file($_FILES['avatar']['tmp_name'], $dossier . $fichier);
						}
						//-----------Cryptage MDP-------------------//
						$password=sha1($password);	
						$ip=$_SERVER["REMOTE_ADDR"];
						$date=date("y.m.d"); 
						//-------------Enregistrement--------------//
						$NewAccount = "
						INSERT INTO `account`(`pseudo`, `password`, `nom`, `prenom`, `mail`, `date_creation`, `last_connexion`, `ip_connexion`, `user_level`, `user_avatar`)
						VALUES ('$pseudo','$password','$nom','$prenom','$mail','$date','$date','$ip','$level','$VarRequete')";
						$Verif = mysqli_query($connexion, $NewAccount)or die( mysqli_error());
						//---------Verification du traitement-----//
						if(!$Verif)
						{
						$check=3;
						}
						else
						{
						$check=4;
						}
					}
					else
					{
						$check="error_mail";
					}
				}
				else
				{
					$check="error_MDP";
				}
			}
		return $check;
	}
	function listing_user()
	{
		global $connexion;
		$alluser = mysqli_query($connexion, "SELECT `id_account`,`pseudo`,`nom`,`prenom`,`user_level`,`mail`,`user_avatar` FROM `account` ");
		while ($req=mysqli_fetch_array($alluser))
			{
				echo "<div class='user'>
							<h4>$req[1]</h4>
							<center><img src='$req[6]'></center>";
							if($req[4]=='administrateur')
							{
								echo"<font color='#0591ff'><center><i class='fa fa-user-secret' aria-hidden='true'></i> Administrateur</center></font>";
							}
							elseif($req[4]=='developpeur')
							{
								echo"<font color='#0591ff'><center><i class='fa fa-cogs' aria-hidden='true'></i><u> Developpeur </u></center></font>";
							}
							else
							{
								echo"<font color='#72243d'><center><i class='fa fa-user' aria-hidden='true'></i> Professeur</center></font>";
							}
							echo"<div class='panel'>
								Nom : $req[2]<p>
								Prénom : $req[3]<p>
								E-mail: <p>$req[5]
							</div>
							<div class='option'>
								<a href='index.php?p=del&id=$req[0]' class='del'><i class='fa fa-times' aria-hidden='true'></i></a>

							</div>
					  </div>";
			}
	}
	function select_chapitre() //modif
	{
		global $connexion;
		$chapitre = mysqli_query($connexion, "SELECT `name_chap`,`classe` FROM `chapitre` where classe='".$_GET['cl']."' ORDER BY id ASC"); //modif
		if(mysqli_num_rows($chapitre) > 0)
		{
			while ($req=mysqli_fetch_array($chapitre))
			{
				echo"<a href=\"index.php?p=cours&cl=".$_GET['cl']."&chap=$req[0]\"><div class='item'><h1>$req[0]</h1></div></a>"; //modif
			}
		}
		else
		{
				echo"<div class='item'><h1>Aucun Cours</h1></div>";
		}
	}	
	function formSelect_chapitre() //modif
	{
		global $connexion;
		$select_chapitre= mysqli_query($connexion, "SELECT name_chap,classe FROM `chapitre`");
		while ($req=mysqli_fetch_array($select_chapitre))
		{
			echo"<option value=\"$req[0]£$req[1]\">$req[0] - $req[1]</option>";
		}
	}
	function create_chapitre($titre,$class) //modif
	{
		global $connexion;
		$titre=preventSqlInjection($titre);
		$write=mysqli_query($connexion, "INSERT INTO `chapitre`(`id`, `name_chap`, `classe`) VALUES ('','$titre','$class')");
		if(!$write)
		{
			$check="false";
		}
		else
		{
			$check="true";
		}
		return $check;
	} //modif
	function page_class()
	{
		global $connexion;
		global $background;
		global $title;
		global $description;		
		$select_info= mysqli_query($connexion, "SELECT * FROM `categories` where classe='".$_GET['cl']."'");
		if(mysqli_num_rows($select_info) > 0)
		{
			while ($req=mysqli_fetch_array($select_info))
			{
				$background=$req[4];
				$title=$req[5];
				$description=$req[6];
			}
		}
	}
	function select_classe()
	{
		global $connexion;
		$select_classe= mysqli_query($connexion, "SELECT classe FROM `categories`");
		while ($req=mysqli_fetch_array($select_classe))
		{
			echo"<option value='$req[0]'>$req[0]</option>";
		}
	}
	function create_cours($title,$chapitre,$file,$classe)//modif
	{	
		global $connexion;
		$title=preventSqlInjection($title);
		$chapitre=preventSqlInjection($chapitre);
		$requete=mysqli_query($connexion, "SELECT id FROM chapitre WHERE name_chap='$chapitre' AND classe='$classe'");
		if (mysqli_num_rows($requete)==1)
		{
			while ($donnee=mysqli_fetch_array($requete)) {
				$id_chap=$donnee[0];	
			}
			
			$dossier_ressource="../assets/ressource/pdf_cours/";
			$file=basename($_FILES['file']['name']);
			$extension = pathinfo($file, PATHINFO_EXTENSION);
			
			$fichier_background=basename($_FILES['file']['name']);
			$liens=$dossier_ressource.$file;
			$var_requete="assets/ressource/pdf_cours/".$file;
			move_uploaded_file($_FILES['file']['tmp_name'], $liens);
			$write=mysqli_query($connexion, "INSERT INTO `cour`( `title`, `url`,`id_chapitre`) VALUES ('$title','$var_requete','$id_chap')");
			if(!$write)
			{
				$check="false";
			}
			else
			{
				$check="true";
			}
		}
		else
		{
			$check="false";
		}	
		return $check;
	} //modif
	function select_cours($cours)//modif
	{
		global $connexion;
		global $pdf;
		$chap=mysqli_query($connexion, "SELECT `id_cour`, `title`, `url` FROM `cour` WHERE id_cour='$cours' ORDER BY title ASC");
		while ($req=mysqli_fetch_array($chap))
		{
			$pdf=$req[2];
		}
	}
	function select_l($chapitre)//modif
	{
		global $connexion;
		preventSqlInjection($chapitre);
		$chapitre=addslashes ($chapitre);
		$chap=mysqli_query($connexion, "SELECT `id_cour`, `title`, `url`,chapitre.name_chap FROM `cour`,`chapitre` WHERE chapitre.name_chap='$chapitre' AND cour.id_chapitre=chapitre.id AND chapitre.classe='".$_GET['cl']."'");
		while ($req=mysqli_fetch_array($chap))
		{
		echo"<a href=\"index.php?p=cours&cl=".$_GET['cl']."&chap=$req[3]&id=$req[0]\"><div class='lecon'";
				if(isset($_GET['id'])&&$_GET['id']==$req[0])
				{
					echo "id=active";
				}
				echo"><i class='fa fa-file-pdf-o' aria-hidden='true'></i> $req[1]</div>";
		}
	}
	function create_ressource($title,$class,$file,$type)
	{
		global $connexion;
		$dossier_ressource="../assets/ressource/file/";
		$file=basename($_FILES['file']['name']);
		$fichier_background=basename($_FILES['file']['name']);
		$liens=$dossier_ressource.$file;
		$var_requete="assets/ressource/file/".$file;
		move_uploaded_file($_FILES['file']['tmp_name'], $liens);
		$write=mysqli_query($connexion, "INSERT INTO `ressource`( `url`, `title`, `classe`,`type`) VALUES ('$var_requete','$title','$class','$type')");
		if(!$write)
		{
			$check="false";
		}
		else
		{
			$check="true";
		}
		return $check;
	}
	function delet_user()
	{
		global $connexion;
		$deletuser = mysqli_query($connexion, "DELETE FROM `account` WHERE id_account='".$_GET['id']."'");	
	}
	function update_user($pseudo,$password,$mail,$name,$prenom,$id)
	{
		global $connexion;
		$password=sha1($password);
		$updateuser= mysqli_query($connexion, "UPDATE `user` SET `login`='$pseudo',`password`='$password',`mail`='$mail',`nom`='$name',`prenom`='$prenom' WHERE id_account='$id'");
	}
	function select_ressource() //modif
	{
		global $connexion;
		$ressource=mysqli_query($connexion, "SELECT `id`, `url`, `title`, `classe`, `type` FROM `ressource` WHERE classe='".$_GET['cl']."'"); 
		echo"<h4>Tous les fichiers</h4><p>";
		if(mysqli_num_rows($ressource)!=0)
		{
			?>
			<table>
			<?php
			$i=2;
			while ($req=mysqli_fetch_array($ressource))
			{
				if(($i%2)==0)
				{
					echo "<tr>";
				}	

				$position=stripos($req[1],'.');
				$suffixe=substr($req[1],$position);

				echo"<td><a href='$req[1]'><div class='ressource'><i class='fa fa-pencil-square-o fa-2x' aria-hidden='true'></i> <br>$req[2]$suffixe</div></a></td>";
				if((($i+1)%2)!=0)
				{
					echo "</tr>";
				}
			}
			?>
			</table>
			<?php
		}	
		else
		{
			echo"<div class='none'>Aucun fichier</div>";
		}
	}
	function liste_cours()//modif
	{
		global $connexion;
		$cours=mysqli_query($connexion, "SELECT `id_cour`, chapitre.classe, `title`, `url`, chapitre.name_chap FROM `cour`,`chapitre` WHERE cour.id_chapitre=chapitre.id  ORDER BY classe DESC");
		echo"
			<table>
				<thead>
					<tr style='background-color:#9888c8;'>
						<td>Titre</td>
						<td>Classe</td>
						<td>Chapitre</td>
						<td>Supprimer</td>
					</tr>
				</thead>";
		while ($req=mysqli_fetch_array($cours))
		{
			if($cours>0)
			{
				echo"<tr>
						<td>$req[2]</td>
						<td>$req[1]</td>
						<td>$req[4]</td>
						<td>Suppresion : <input type='checkbox' name=a[] value='$req[0]'></td>
					</tr>";
			}
		}
		echo"</table>";
	}
	function delet_cours($id)
	{
		global $connexion;
		$delet="DELETE FROM `cour` WHERE id_cour  = '$id'";
		$verif=mysqli_query ($connexion, $delet);
	}
	function liste_ressources()
	{
		global $connexion;
		$cours=mysqli_query($connexion, "SELECT `id`, `url`, `title`, `classe`, `type` FROM `ressource` ORDER BY classe DESC");
		echo"
			<table>
				<thead>
					<tr style='background-color:#9888c8;'>
						<td>Titre</td>
						<td>Classe</td>
						<td>Type</td>
						<td>Supprimer</td>
					</tr>
				</thead>";
		while ($req=mysqli_fetch_array($cours))
		{
			if($cours>0)
			{
				echo"<tr>
						<td>$req[2]</td>
						<td>$req[3]</td>
						<td>$req[4]</td>
						<td>Suppresion : <input type='checkbox' name=a[] value='$req[0]'></td>
					</tr>";
			}
		}
		echo"</table>";

	}
	function liste_onglets()
	{
		global $connexion;
		$cours=mysqli_query($connexion, "SELECT `id_categorie`, `classe`, `title` FROM `categories` ORDER BY classe DESC");
		echo"
			<table>
				<thead>
					<tr style='background-color:#9888c8;'>
						<td>Nom</td>
						<td>Titre</td>
						<td>Supprimer</td>
					</tr>
				</thead>";
		while ($req=mysqli_fetch_array($cours))
		{
			if($cours>0)
			{
				echo"<tr>
						<td>$req[1]</td>
						<td>$req[2]</td>
						<td>Suppresion : <input type='checkbox' name=a[] value='$req[0]'></td>
					</tr>";
			}
		}
		echo"</table>";
	}
	function liste_actualite() //josh
	{
		global $connexion;
		$actualite=mysqli_query($connexion, "SELECT * FROM `news`");
		echo"
			<table>
				<thead>
					<tr style='background-color:#9888c8;'>
						<td>Titre</td>
						<td>Description</td>
						<td>Date de debut d'actualité</td>
						<td>date de fin d'actualité</td>
						<td>Supprimer</td>
					</tr>
				</thead>";
		while ($req=mysqli_fetch_array($actualite))
		{
			if($actualite>0)
			{
				echo"<tr>
						<td>$req[1]</td>
						<td>$req[2]</td>
						<td>$req[3]</td>
						<td>$req[4]</td>
						<td>Suppresion : <input type='checkbox' name=a[] value='$req[0]'></td>

					</tr>";
			}
		}
		echo"</table>";

	}
	function liste_chapitre() //modif
	{
		global $connexion;
		$actualite=mysqli_query($connexion, "SELECT * FROM `chapitre` ORDER BY classe DESC");
		echo"
			<table>
				<thead>
					<tr style='background-color:#9888c8;'>
						<td>Chapitre</td>
						<td>Classe</td>
						<td>Supprimer</td>
					</tr>
				</thead>";
		while ($req=mysqli_fetch_array($actualite))
		{
			if($actualite>0)
			{
				echo"<tr>
						<td>$req[1]</td>
						<td>$req[2]</td>
						<td>Suppresion : <input type='checkbox' name=a[] value='$req[0]'></td>

					</tr>";
			}
		}
		echo"</table>";

	}
	function delet_chapitre($id) //josh
	{
		global $connexion;
		$delet="DELETE FROM `chapitre` WHERE id='$id'";
		$verif=mysqli_query ($connexion, $delet);
	}
	function delet_actu($id) //josh
	{
		global $connexion;
		$delet="DELETE FROM `news` WHERE id_news='$id'";
		$verif=mysqli_query ($connexion, $delet);
	}
	function delet_ressources($id)
	{
		global $connexion;
		$delet="DELETE FROM `ressource` WHERE id  = '$id'";
		$verif=mysqli_query ($connexion, $delet);
	}
	function delet_onglets($id)
	{
		global $connexion;
		$delet="DELETE FROM `categories` WHERE id_categorie  = '$id'";
		$verif=mysqli_query ($connexion, $delet);
	}
	function delet_allcours($id)
	{
		global $connexion;
		$cours=mysqli_query($connexion, "SELECT `classe` FROM `categories` where id_categorie='$id'");
		while ($req=mysqli_fetch_array($cours))
		{
			$suppr="DELETE * FROM `cour` WHERE classe  = '$req[0]'";
		}
	}
	function select_user()
	{
		global $connexion;
		global $pseudo;
		$user=mysqli_query($connexion, "SELECT `pseudo`, `id_account` FROM `account` WHERE id_account='".$_GET['id']."'");
		while ($req=mysqli_fetch_array($user))
		{
			$pseudo=$req[0];
		}
	}
	function updade_password($password,$password_verif,$user)
	{
		global $connexion;
		if($password==$password_verif)
		{
			$password=sha1($password);
			$update=mysqli_query($connexion, "UPDATE `account` SET password='$password' where pseudo='$user'");
			$check='true';
		}
		else
		{
			$check='false';
		}
	}
	function updade_info($nom, $prenom, $mail,$user)
	{
		global $connexion;
			$update=mysqli_query($connexion, "UPDATE `account` SET `nom`='$nom',`prenom`='$prenom',`mail`='$mail'where pseudo='$user'");
	}
	function updade_user($nom, $prenom, $mail,$user)
	{
		global $connexion;
			$update=mysqli_query($connexion, "UPDATE `account` SET `pseudo`='$pseudo',`nom`='$nom',`prenom`='$prenom',`mail`='$mail'where pseudo='".$_GET['id']."'");
	}

	function preventSqlInjection($toEscape)
	{
		global $connexion;
		return mysqli_real_escape_string($connexion, $toEscape);
	}

	function add_news($title,$information,$date_debut,$date_fin)
	{    
		global $connexion;
		$title = preventSqlInjection($title);
		$information = preventSqlInjection($information);


		$requete = "INSERT INTO `news`(`title`, `description`,`date_debut`,`date_fin`) VALUES ('$title','$information','$date_debut','$date_fin')";

		if($news=mysqli_query($connexion, $requete))
		{
			$etat='true';
		}
		else
		{
			$etat='false';
		}
		return $etat;
	}
	function read_news()
	{
		global $connexion;
		$news=mysqli_query($connexion, "SELECT * FROM `news`");
		if(mysqli_num_rows($news)!=0)
		{
			while ($req=mysqli_fetch_array($news))
			{
				$date='20'.date("y-m-d").'';
				if ($date >= $req[3] && $req[4] >= $date )
				{

					echo"<div class='actu'>- $req[3] : <strong>$req[1]</strong> - $req[2]</div>";
				}
			}
		}
		else
		{
			echo"<div class='actu'>-Aucune actualité</div>";
		}
	}
	function printnews() //modif
	{
		global $connexion;
		$printNews = mysqli_query($connexion, "SELECT * FROM `news`");
?>
		<div id="globalNews">
<?php
		while ($req=mysqli_fetch_array($printNews))
		{
			$date='20'.date("y-m-d").'';
			if ($date >= $req[3] && $req[4] >= $date )
			{
			?>
				<div id="afficherNews">
					<div id="titreNews"><?php echo "$req[1]"; ?></div>
					<div id="descriptionNews"><?php echo "$req[2]"; ?></div>
				</div>
			<?php
			}		
		}
?>
		</div>
<?php
	}
	function select_max()
	{
		global $connexion;
		$max=@mysqli_query($connexion, "SELECT MAX(id_cour) FROM cour");
		while ($req=@mysqli_fetch_array($max))
		{
			if(empty($req[0]))
			{
				$max=0;
			}
			else
			{
			$max=$req[0];
			}
		}
		
		return $max;
	}
	function select_max_ressource()
	{
		global $connexion;
		$max=@mysqli_query($connexion, "SELECT MAX(id) FROM ressource");
		while ($req=@mysqli_fetch_array($max))
		{
			$max=$req[0];	
		}
		return $max;
	}
	function close_bdd()
	{
		mysqli_close($GLOBALS["connexion"]);	
	}
	
	?> 

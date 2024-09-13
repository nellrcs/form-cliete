<?php
 /* 
 *  @author    Warllen castro dos santos.
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 *  @version 2.3
 */	

class Conexao
{
	private $banco = DBBASE;
	private $host = DBHOST;
	private $user = DBUSER;
	private $password = DBPASS;
	private $conexao = DBCONEXAO;
	private $conex;
  
    public $limiteItens = false;
    public $paginaAtual;
    public $resultPaginacao;
    public $totalItens;

	function __construct() 
	{
		if($this->conexao == "mysql"):
			$this->com_mySql();	
		else:
			$this->com_pDo();	
		endif;
	}


	public function com_pDo()
	{
		try 
		{	
			$dsn = "mysql:dbname=".$this->banco.";host=".$this->host;
			$dbh = new PDO($dsn,$this->user,$this->password );
			$this->conex = $dbh;
			$this->conex->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
			return $this->conex;
		}
		catch ( PDOException $e ) 
		{
			echo 'ERRO: ' . $e->getMessage();
			return false;
		}
	}

	public function com_mySql()
	{
		$conexao = mysql_connect($this->host,$this->user,$this->password) or die(false);
        $banco = mysql_select_db($this->banco,$conexao) or die(false);
	}

	# SELECT tabela | criterio especificao | campos resultado | odrder ASC ou DESC | Limite de campos
	public function seleciona($tabela,$where = array(),$campos = array(), $orden = array() ,$limite = null)
	{
		try
		{
			
            
            $string = "SELECT ";
			$contador = $string;
            if(count($campos) > 0):
				$i = 1;
				foreach ($campos as $campo)
				{
					$string .= $campo;
					if ($i < count($campos))
					{
						$string .= ', ';
					}
					$i++;
				}
			else:
				$string .= " * ";
			endif;
            
            
			$string .= " FROM ".$tabela;
            $contador .= "count(*) as total FROM ".$tabela;
            
			if(count($where) > 0):
				$string .= " WHERE ";
                $contador .= " WHERE ";
				$i = 1;
				foreach($where as $nome => $valor)
				{
					
					# o where = 'valor1,valor2,valor3'	Eh = IN(valor1,valor2,valor3)
					# o where = 'valor1|valor2|valor3'	Eh = LIKE valor1 OR LIKE valor2 OR LIKE valor3)

					if(strpos($valor,',') !== false)
					{
						$string .= $nome." IN (".$valor.") ";
                        $contador .=  $nome." IN (".$valor.") ";
					}	
					elseif(strpos($valor,'|') !== false)
					{

						$vli = explode('|', $valor);
						$esp = array_filter($vli);
						$e = 1;
						foreach($esp as $v) {
		
							$string .= $nome." LIKE '".$v."' ";
                    		$contador .=  $nome." LIKE '".$v."' ";	

                    		if ($e != count($esp ))
							{
								$string .= " OR ";
                        		$contador .=  " OR ";	
							}
							$e++;

						}
					}
					else
					{
						$string .= $nome."='".$valor."'";
                    	$contador .= $nome."='".$valor."'";
					}	
					
					
					if ($i != count($where))
					{
						$string .= ' AND ';
                        $contador .= ' AND ';
					}
					$i++;
				}
			endif;

			if(!empty($orden)):
				if(!empty($orden['ASC'])):
					$string .= " ORDER BY ".$orden['ASC']." ASC ";
				elseif(!empty($orden['DESC'])):
					$string .= " ORDER BY ".$orden['DESC']." DESC ";
				elseif(!empty($orden['RAND'])):
					$string .= " ORDER BY rand() ";	
				endif;	
			endif;	
            
			$this->totalItens = $this->selecionaBasico($contador);

            if($this->limiteItens):
                $this->resultPaginacao = $this->paginar($this->limiteItens, $this->totalItens[0]->total,$this->paginaAtual);
                $limite = $this->resultPaginacao->inicia.",".$this->resultPaginacao->fim;
            endif;
            
			if(!empty($limite)):
				$string .= " limit ".$limite;
			endif;


			$aR = array();
            
            //-----------------------------------
			if($this->conexao == "mysql"):
				$select = mysql_query($string);		
				while($lista = mysql_fetch_object($select)):
					$aR[] = $lista;
				endwhile;
			else:
				$banco = $this->conex->prepare($string);
				$banco->execute();
				while($lista = $banco->fetch(PDO::FETCH_OBJ)):
					$aR[] = $lista;
				endwhile;
			endif;

			//-----------------------------------
      
            //echo "|".$string;
            ////////////
			return $aR;
		}
		catch(Execption $e) 
		{
			echo "ERRO:" . $e->getMessage();
			return false; 
		}
	}

	# SELECT query personalizada
	public function selecionaBasico($query)
	{
		try
		{
			$string = $query;
			
			$aR = array();
			//-----------------------------------
			if($this->conexao == "mysql"):
				$select = mysql_query($string);		
				while($lista = mysql_fetch_object($select)):
					$aR[] = $lista;
				endwhile;
			else:
				$banco = $this->conex->prepare($string);
				$banco->execute();
				while($lista = $banco->fetch(PDO::FETCH_OBJ)):
					$aR[] = $lista;
				endwhile;
			endif;

			return $aR;
		}
		catch(Execption $e) 
		{
			echo "ERRO:" . $e->getMessage();
			return false; 
		}
	}

	# INSERIR nome tabela | campos 
	public function inserir($tabela,$campos = array())
	{
		try 
		{
			$string = "INSERT INTO ". $tabela;
			$string .= " (";
			$i = 1;
			foreach($campos as $nome => $valor)
			{
				$string .= $nome;
				if ($i != count($campos))
				{
					$string .= ', ';
				}
				$i++;
			}
			$string .= ") ";
			$string .= " VALUES (";
			$i = 1;
			foreach($campos as $nome => $valor)
			{
				$string .= '\''.addslashes($valor) . '\' ';
				if ($i != count($campos))
				{
					$string .= ', ';
				}
				$i++;
			}
			$string .= ") ";

			return $this->execulta($string,true);

		} 
		catch(Execption $e) 
		{
			echo "ERRO:" . $e->getMessage();
			return false; 
		}
	}

	# ATUALIZA nome tabela | campos que serao atualizados | criterio especificao
	public function atualiza($tabela,$campos = array(),$where = array()){
		try{
			$string = "UPDATE ". $tabela . " SET ";
			$i = 1;
			foreach($campos as $campo => $valor){
				$string .= $campo."=".'\''.addslashes($valor).'\'';
				if ($i!= count($campos))
				{
					$string .= ', ';
				}
				$i++;
			}
			$string .= " WHERE ";
			$i = 1;
			foreach($where as $nome => $valor){
				$string .= $nome. "=".'\''.addslashes($valor).'\'';
				if ($i != count($where))
				{
					$string .= ' and ';
				}
				$i++;
			}
			$this->execulta($string);
		} 
		catch(Execption $e) 
		{
			echo "ERRO:" . $e->getMessage();
			return false; 
		}
	}

	# APAGA nome tabela | criterio especificao
	public function apaga($tabela,$where = array()){
		try{	
			$string = "DELETE FROM ".$tabela;
			$string .= " WHERE ";
			$i = 1;
			foreach($where as $nome => $valor){
				$string .= $nome. "=".'\''.addslashes($valor).'\'';
				if ($i != count($where)){
					$string .= ' and ';
				}
				$i++;
			}
			$this->execulta($string);
		}catch(Execption $e){
			echo "ERRO:" . $e->getMessage();
			return false; 
		}
	}

	#execulta um insert ou querry especifica e retorna um ID
	public function execulta($mysql_string,$id = false)
	{

		if($this->conexao == "mysql"):
			$quer = mysql_query($mysql_string);
			if($id):	
				return mysql_insert_id();
			else:
				return $quer;
			endif;	
		else:
			$banco = $this->conex->prepare($mysql_string);
			$banco->execute();
			if($id):
				return $this->conex->lastInsertId();
			else:
				return $banco;
			endif;			
		endif;	

	}

	# VERIFICA SE TABELA EXISTE nome tabela | campos a criar | se for true cria a tabela

	public function verifica_tabela($tabela,$sql_array = array(),$cria_tabela = false)
	{
		
		if($tabela != false):
			if($this->conexao == "mysql"):
				$tab = mysql_num_rows($this->execulta("SHOW TABLES LIKE '".$tabela."'"));
			else:
				$tab = $this->execulta("SHOW TABLES LIKE '".$tabela."'")->rowCount();
			endif;

			if($tab > 0)
			{
				return true;
			}
			else		
			{
				if($cria_tabela)
				{
					foreach ($sql_array as $sql){
						$this->cria_tebela($sql);
					}
					return true;
				}
				else
				{
					return false;
				}	
			}
		endif;		
	}

	# tipos de campos a serem criados
	public function tipo_campos($campo,$tipo)
	{
		switch($tipo) 
		{
			case 0:
				//inteiro, id
				return "`".$campo."` int(11) DEFAULT NULL,";
			break;
			case 1:
				//titulo,palavra,string
				return "`".$campo."` varchar(255) DEFAULT NULL,";
			break;
			case 2:
				//textos
				return "`".$campo."` text,";
			break;
			case 3:
				//data e time
				return "`".$campo."` timestamp NULL DEFAULT CURRENT_TIMESTAMP,";
			break;
			case 4:
				//preco
				return "`".$campo."` decimal(15,4) NOT NULL DEFAULT '0.0000',";
			break;
            case 5:
				//data
				return "`".$campo."` date DEFAULT NULL,";
			break;
			default:
				return "`".$campo."` varchar(255) DEFAULT NULL,";
			break;
		}
	}

	# cria uma tabela
	public function cria_tebela($ar_tab)
	{
			
			///EXEMPLO///
			//$ar_tab = array('tabela'=>array('data'=>3,'titulo'=>1,'texto'=>2));
			/////////////

			foreach ($ar_tab as $tab => $n) 
			{
				$string = "CREATE TABLE IF NOT EXISTS `".$tab."` (`ID` int(11) NOT NULL AUTO_INCREMENT, ";
                                $string .= "`orden` int(11) DEFAULT NULL, ";
				foreach ($n as $c => $v) 
				{
					$string .= $this->tipo_campos($c,$v);
				}	
				$string .= "PRIMARY KEY (`ID`)";
				$string .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8";
			}	
			return $this->execulta($string);
	}


    # paginacao
    public function paginar( $registros_por_pagina , $total_registros , $posicao_atual )
    {
        $obj = new stdClass();
        
        #define aposicao atual como 1
        if($posicao_atual == 0)
        {
           $posicao_atual = 1;
        }  
        #retorna o numero de paginas em um  valor inteiro   
        $paginacao = ceil($total_registros / $registros_por_pagina);  
        
        #resto to total 
        if($total_registros < ( $paginacao * $registros_por_pagina ))
        {
            $resto = ( $paginacao * $registros_por_pagina ) - $total_registros;           
        }
        else
        {
            $resto = false;
        }
       #posicao do contador  
       $limita = $posicao_atual * $registros_por_pagina;
       $inicia = $limita - $registros_por_pagina;
       if($resto == true){
            if($posicao_atual == $paginacao)
            {           
                $limita = $limita - $resto;
                $inicia = ( $paginacao - 1 ) * $registros_por_pagina;
            }
       } 
        
        $obj->paginas = false;       
        $obj->limita = false;
        $obj->inicia = false;
        $obj->fim = $registros_por_pagina;
        $obj->proxima = false;
        $obj->anterior = false;
        $obj->total = $total_registros;
        #retorna numero de paginas
        $obj->paginas = $paginacao;
        #retorna posicao final do contator
        $obj->limita = $limita;
        
        #retorna posicao inicial  do contator
        $obj->inicia = $inicia;
        #retorna proxima pagina
        if( $posicao_atual == $paginacao )
        {    
            $obj->proxima = $posicao_atual;
        }
        else
        {
            $obj->proxima = $posicao_atual+1;
        }  

        #retorna pagina anterior
        if( $posicao_atual <= 1)
        {
            $obj->anterior = false;                
        }
        else
        {    
            $obj->anterior = $posicao_atual - 1;
        }
        return $obj;   
    }        
 
    
}


?>

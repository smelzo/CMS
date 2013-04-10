#cs
------------------------------------------------------------------------
		stampa protocollo cassa edile
        © MASSIMO CARNEVALI 
------------------------------------------------------------------------
#CE

Local $sysid=$CmdLine[1]
Local $mitt=$CmdLine[2]
Local $dtri=$CmdLine[3]

Dim $objXL

; Genero pdf da foglio excel
$objXL = ObjCreate("Excel.Application")

	With $objXL.Application
		.Workbooks.Open ("\\pdc1\exe\spprot.xlsm")
		$x = .Run("Stampa", $sysid, $mitt, $dtri)
		.Quit
	EndWith

; esporta system id

$Percorso = "C:\Cassaedile\"&$sysid
Local $SDK=ObjCreate("AFSDK.Esporta")
$SDK.SystemID = $sysid
$SDK.EsportaPath = $Percorso
$SDK.EsportaNome = "sysid.pdf"
local $result
$result=$SDK.EsportaDoc

; controllare se esistono 2 file su cartella
$elab = " " 
$exel = " "
$arxi = " "
while $elab = 'S'
If FileExists($percorso & "\excel.pdf") Then $exel = "S"
If FileExists($percorso & "\sysid.pdf") Then $arxi = "S"
if $exel = "S" AND $arxi = "S" Then $elab="S"
Sleep (500)
WEnd

; creare unico pdf

$comando = "pdftk "&$Percorso&"\excel.pdf "&$Percorso&"\sysid.pdf cat output "&$Percorso&"\uno.pdf"
Run ($comando)
Sleep (2000)

; stampa pdf
ShellExecute("uno.pdf", "", $percorso , "print")
Sleep (500)

; $comando = "pskill AcroRd32.exe"
; Run ($comando)
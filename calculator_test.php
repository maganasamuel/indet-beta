<html>
    <head>
    	<title>Calculator Test</title>
    </head>
<body>
	<table>
		<tr>
			<td colspan="3"><input type="text" id="txtInput"></td>
		</tr>
		<tr>
			<td colspan="3"><input type="text" id="storedValue" readonly style="background-color: #CDCDCD; color:white;"></td>
		</tr>
		<tr>
			<td><button onclick="Add()">+</button></td>
			<td><button onclick="Subtract()">-</button></td>
			<td><button onclick="Multiply()">*</button></td>
		</tr>
		<tr>
			<td><button onclick="Divide()">/</button></td>
			<td><button onclick="Equals()">=</button></td>
			<td><button onclick="Clear()">C</button></td>
		</tr>
	</table>

	<script>
		var storedNum = null;
		var input = document.getElementById("txtInput");
		var storedProcedure = null;

		function StoreNum(){
			storedNum = parseFloat(input.value);
			input.value = "";
		}

		function Add(){
			if(storedNum == null){
				StoreNum();
				storedProcedure = "Add";
			}
			else{
				input.value = RunProcedure();
				Add();
			}
		}

		function Subtract(){
			if(storedNum == null){
				StoreNum();
				storedProcedure = "Subtract";
			}
			else{
				input.value = RunProcedure();
				Subtract();
			}
		}

		function Multiply(){
			if(storedNum == null){
				StoreNum();
				storedProcedure = "Multiply";
			}
			else{
				input.value = RunProcedure();
				Multiply();
			}
		}

		function Divide(){
			if(storedNum == null){
				StoreNum();
				storedProcedure = "Divide";
			}
			else{
				input.value = RunProcedure();
				Divide();
			}
		}

		function Equals(){
			input.value = RunProcedure();
		}

		function Clear(){
				input.value = "";
				storedNum = null;
				storedProcedure = null;
		}

		function RunProcedure(){
			var op = 0;
			if(storedProcedure==null)
				return 0;

			switch(storedProcedure) {
			  case "Add":
			    	op = storedNum + parseFloat(input.value);
			    break;
			  case "Subtract":
			    	op = storedNum - parseFloat(input.value);
			    break;
			  case "Multiply":
			    	op = storedNum * parseFloat(input.value);
			    break;
			  case "Divide":
			    	op = storedNum / parseFloat(input.value);
			    break;

			  default:
			    op = 0;
			} 
			storedNum = null;

			console.log("Result is " + op);
			return op;
		}
	</script>
</body>

</html>
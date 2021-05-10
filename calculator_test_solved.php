<html>
    <head>
    	<title>Calculator Test</title>
    </head>
<body>
	<!-- Calculator Layout -->
	<table>
		<tr>
			<td colspan="3"><input type="text" id="txtInput"></td>
		</tr>
		<tr>
			<td colspan="3"><input type="text" id="storedValue" readonly style="background-color: #CDCDCD; color:white;"></td>
		</tr>
		<tr>
			<td><button onclick="VariableProcedure('Add')">+</button></td>
			<td><button onclick="VariableProcedure('Subtract')">-</button></td>
			<td><button onclick="VariableProcedure('Multiply')">*</button></td>
		</tr>
		<tr>
			<td><button onclick="VariableProcedure('Divide')">/</button></td>
			<td><button onclick="Equals()">=</button></td>
			<td><button onclick="Clear()">C</button></td>
		</tr>
	</table>

	<script>
		var storedNum = null;
		var input = document.getElementById("txtInput");
		var storedInput = document.getElementById("storedValue");
		var storedProcedure = null;

		function StoreNum(procedure){
			storedNum = parseFloat(input.value);
			input.value = "";
			storedInput.value = storedNum;
			storedProcedure = procedure;
		}

		function VariableProcedure(procedure){
			if(storedNum == null){
				StoreNum(procedure);
			}
			else{
				Equals();
				VariableProcedure(procedure);
			}
		}

		function Equals(){
			input.value = RunProcedure();
			storedInput.value = "";
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
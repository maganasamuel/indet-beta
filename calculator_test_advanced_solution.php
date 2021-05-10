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
			<td><button style="width:100%;" onclick="VariableProcedure('+')">+</button></td>
			<td><button style="width:100%;" onclick="VariableProcedure('-')">-</button></td>
			<td><button style="width:100%;" onclick="VariableProcedure('*')">*</button></td>
		</tr>
		<tr>
			<td><button style="width:100%;" onclick="VariableProcedure('/')">/</button></td>
			<td><button style="width:100%;" onclick="Equals()">=</button></td>
			<td><button style="width:100%;" onclick="Clear()">C</button></td>
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
			if(storedNum==null){
				alert("Input a number first");
				return;
			}
			var curr_input = parseFloat(input.value);
			if(isNaN(curr_input))
				curr_input = 0;
			input.value = eval(storedNum + " " + storedProcedure + " " + curr_input);
			storedNum = null;
			console.log("Result is " + input.value);
			storedInput.value = "";
		}

		function Clear(){
			input.value = "";
			storedNum = null;
			storedProcedure = null;
		}
	</script>
</body>

</html>
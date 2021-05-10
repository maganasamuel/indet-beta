<script>
//--- Calculator Method Begin -------->
	
    jQuery(document).ready(function($) {  
    //-------variable init
  
       var percent_defensive = (2.17 / 100);
       var percent_conservative = (4.65 / 100);
       var percent_balanced = (6.84 / 100);
       var percent_growth = (8.89 / 100);
        
    //--------Chart Column Colors 
    //valid color format sample: 
    //colorName:    red...orange..teal...etc
    //hex:          #dcdcdc...
    //rgb:          rgb(92, 218, 180)
    //rgba:         rgba(255, 0, 0, 0.2) 
       
      color_defensive = "rgba(21, 202, 205, 0.90)";          
      color_conservative = "rgba(221, 32, 151, 0.90)";
      color_balanced = "rgba(36, 196, 250, 0.90)";
      color_growth = "rgba(242, 242, 35, 0.90)";
        
        
        
        
        
      show_chart(employed_cal(30, 55000, 15000, 3, 3));
  
      $('#self-employed').prop('disabled', true);
      $('#work_status').on('change', function() {
  
          $('#self-employed').prop('disabled', true);
          if (this.value == 'employed') {
              $("#employed-wrapper").removeClass("hidden");
              $('#employed').prop('disabled', false);
  
              $("#self-wrapper").addClass("hidden");
              $('#self-employed').prop('disabled', true);
          } else if (this.value == 'self-employed') {
              $("#self-wrapper").removeClass("hidden");
              $('#self-employed').prop('disabled', false);
  
              $("#employed-wrapper").addClass("hidden");
              $('#employed').prop('disabled', true);
          }
          
      });
  
  
  
          $('#calculator').on('submit', function(e) {
              e.preventDefault();
              var dataArray = $("#calculator").serializeArray();
              var len = dataArray.length;
              var dataObj = {};
  
  
              for (i = 0; i < len; i++) {
                  dataObj[dataArray[i].name] = dataArray[i].value;
              }
  
              console.log(dataArray);
              var age = dataObj['age'];
              var work_status = dataObj['work_status'];
              var income = dataObj['income'];
              var balance = dataObj['balance'];
              var employee = dataObj['employee_contrib'];
              var employer = dataObj['employer_contrib'];
              var con_basis = dataObj['con_basis']; //self-employed only
  
  
              if (check_age(age) == true) {
                  if (work_status == "employed") {
                      show_chart(employed_cal(age, income, balance, employee, employer));
                  } else {
                      show_chart(self_employed_cal(age, income, balance, con_basis));
                  }
              } else {
                  console.log("valid age: 16-65");
              }
  
          });
  
  
      function check_age(age) {
          if (age < 16) {
              var error_message = "Sorry, minimum calculation age is 16!";
              $('#validation-errors span').empty().append(error_message);
              return false;
          } else if (age > 65) {
              var error_message = "Sorry, maximum calculation age is 65!";
              $('#validation-errors span').empty().append(error_message);
              return false;
          } else {
              $('#validation-errors span').empty();
              return true;
          }
      }
  
  
  
      function yAxisLabelsFormatter(obj) {
          if (obj.value >= 1000000000000000000)
              return (obj.value / 1000000000000000000) + 'E';
  
          else if (obj.value >= 1000000000000000)
              return (obj.value / 1000000000000000) + 'P';
  
          else if (obj.value >= 1000000000000)
              return (obj.value / 1000000000000) + 'T';
  
          else if (obj.value >= 1000000000)
              return (obj.value / 1000000000) + 'B';
  
          else if (obj.value >= 1000000)
              return (obj.value / 1000000) + 'M';
  
          else if (obj.value >= 1000)
              return (obj.value / 1000) + 'k';
          else
              return obj.value;
      }
  
      function show_chart(arr) {
  
          defensive = arr[0];
          conservative = arr[1];
          balanced = arr[2];
          growth = arr[3];
  
          Highcharts.setOptions({
              lang: {
                  thousandsSep: ','
              }
          });
  
  
          Highcharts.chart('saver_chart', {
  
              credits: {
                  enabled: false
              },
              tooltip: {
                  enabled: false
              },
              chart: {
                  backgroundColor: 'transparent',
                  type: 'column'
              },
              title: {
                  text: null
              },
              subtitle: {
                  text: null
              },
              xAxis: {
                  type: 'category',
                  labels: {
                      style: {
                          color: 'white',
                          fontSize: '1.0rem',
                          fontWeight: 'bold'
                      }
                  }
              },
              yAxis: {
                  title: {
                      text: null
                  },
                  labels: {
                      formatter: function() {
                          return yAxisLabelsFormatter(this);
                      },
                      style: {
                          color: '#e9e9e9',
                          fontSize: '1.0rem'
                      }
                  }
  
              },
              legend: {
                  enabled: false
              },
              plotOptions: {
                  series: {
                      pointPadding: 0.2,
                      groupPadding: 0,
                      animation: {
                          duration: 300
                      },
                      borderWidth: 0,
                      dataLabels: {
                          enabled: true,
                          fontWeight: 'bold',
                          y: 35,
                          style: {
                              textOutline: false,
                              fontSize: '1.0rem',
                          },
                          formatter: function() {
  
                              var ret;
                              if (this.y >= 1000) {
                                  ret = Highcharts.numberFormat(this.y / 1000, 0) + "k";
                              } else if (this.y > 500 && this.y < 1000) {
                                  ret = '1k';
                              } else if (this.y > 100 && this.y < 500) {
                                  ret = '0k';
                              } else if (this.y < 100) {
                                  ret = parseInt(this.y) + "k";
                              }
                              return '$' + (ret ? ret : this.y);
  
  
                          }
                      }
                  }
              },
  
              series: [{
                  name: 'Fund Type',
                  colorByPoint: true,
                  data: [{
                          name: 'Defensive Fund',
                          color: color_defensive,
                          y: defensive
                      },
                      {
                          name: 'Conservative Fund',
                          color: color_conservative,
                          y: conservative
                      },
                      {
                          name: 'Balanced Fund',
                          color: color_balanced,
                          y: balanced
                      },
                      {
                          name: 'Growth Fund',
                          color: color_growth,
                          y: growth
                      }
                  ]
              }]
          });
  
      }
  
  
      function employed_cal(age, income_val, balance, employee, employer) {
          age = parseInt(age);
          income = parseInt(income_val);
          comulative_saving = parseInt(balance);
          employee_cont = employee;
          employer_cont = employer;
  
  
          if (income <= 16800) {
              esct = (10.50 / 100)
          } else if (income > 16800 && income <= 57600) {
              esct = (17.50 / 100)
          } else if (income > 57600 && income <= 84000) {
              esct = (30 / 100)
          } else if (income > 84000) {
              esct = (33 / 100)
          }
  
          e3 = (employee_cont / 100) // 3% Employee Contribution
          g3 = (employer_cont / 100) // 3% Employer Contribution
          f16 = esct // ESCT [tax credit] 
          h4 = 1042.86
          h3 = 521.43 //tax_credit
          k1 = (2 / 100) //----variable1 -> 2
  
          k3 = percent_defensive
          m3 = percent_conservative
          o3 = percent_balanced
          q3 = percent_growth
       
       
          k15 = comulative_saving; //cumulative saving
  
          c16 = income
          d = 0
          e16 = 0
          g16 = 0
          h16 = 0
  
  
          return1 = comulative_saving
          l15 = return1 * (k3 - k1)
  
          return2 = comulative_saving
          n15 = return2 * (m3 - k1)
  
          return3 = comulative_saving
          p15 = return3 * (o3 - k1)
  
          return4 = comulative_saving
          r15 = return4 * (q3 - k1)
  
          esct2 = 0
  
          var counter = 0;
          for (v = (age + 1); v <= 65; v++) {
              c16 += d
  
              if (c16 <= 16800) {
                  esct2 = (10.50 / 100)
              } else if (c16 > 16800 && c16 <= 57600) {
                  esct2 = (17.50 / 100)
              } else if (c16 > 57600 && c16 <= 84000) {
                  esct2 = (30 / 100)
              } else if (c16 > 84000) {
                  esct2 = (33 / 100)
              }
  
              d = (c16 * (3 / 100)) //income
              e16 = (c16 * e3) //employee_contribution
  
              if (age == 16) {
                  if (counter < 2 && age < 18) {
                      g16 = 0 //employer_contribution
                      h16 = 0 //tax_credit
                  } else {
                      g16 = (c16 * g3 * (1 - esct2));
  
                      if (e16 < h4) {
                          h16 = (e16 * 0.5);
                      } else {
                          h16 = h3;
                      }
                  }
  
              } else if (age == 17) {
  
                  if (counter < 1 && age < 18) {
                      g16 = 0 //employer_contribution
                      h16 = 0 //tax_credit
                  } else {
                      g16 = (c16 * g3 * (1 - esct2))
                      if (e16 < h4) {
                          h16 = (e16 * 0.5);
                      } else {
                          h16 = h3;
                      }
                  }
  
              } else {
                  g16 = (c16 * g3 * (1 - esct2)) //employer_contribution
  
                  if (e16 < h4) {
                      h16 = (e16 * 0.5);
                  } else {
                      h16 = h3;
                  }
  
              }
  
              comulative_saving += (e16 + g16 + h16)
  
  
              return1 += (e16 + g16 + h16 + l15)
              l15 = return1 * (k3 - k1)
  
  
              return2 += e16 + g16 + h16 + n15
              n15 = return2 * (m3 - k1)
  
              return3 += e16 + g16 + h16 + p15
              p15 = return3 * (o3 - k1)
  
              return4 += e16 + g16 + h16 + r15
              r15 = return4 * (q3 - k1)
  
              counter++
          }
  
          console.log([minimal(return1), minimal(return2), minimal(return3), minimal(return4)]);
          return ([minimal(return1), minimal(return2), minimal(return3), minimal(return4)])
  
      }
  
  
      function self_employed_cal(age, contrib, balance, con_basis) {
          console.log(age, contrib, balance, con_basis);
  
          age = parseInt(age);
          comulative_saving = parseInt(balance);
          contribution = parseInt(contrib);
  
          if (con_basis == 'weekly') {
              multiplier = 52.1429;
          } else {
              multiplier = parseInt(12); //monthly
          }
  
          k1 = (2 / 100);
          k3 = percent_defensive
          m3 = percent_conservative
          o3 = percent_balanced
          q3 = percent_growth
  
       
          growth_inc_percentage = (3 / 100); // d3 growth income percentage(3%)
          tax_credit_init_1 = 521.43; // h3
          tax_credit_init_2 = 1042.86; // h4
  
          growth_income = 0; // d
          income = (contribution * multiplier); //1042.86          	# c16
          employee_contrib = income; // e16
          employer_contrib = 0; // g16
  
  
          if (employee_contrib < tax_credit_init_2) { // if e16 < h4
              tax_credit = (employee_contrib * 0.5);
              // h16 = e16 * 0.5
          } else {
              tax_credit = tax_credit_init_1; // h16 = h3
          }
  
          return1 = comulative_saving;
          l15 = return1 * (k3 - k1);
  
          return2 = comulative_saving;
          n15 = return2 * (m3 - k1);
  
          return3 = comulative_saving;
          p15 = return3 * (o3 - k1);
  
          return4 = comulative_saving;
          r15 = return4 * (q3 - k1);
  
  
          var counter = 0;
          for (v = (age + 1); v <= 65; v++) {
  
              income += growth_income;
  
              growth_income = (income * growth_inc_percentage);
  
              employee_contrib = income;
  
              if (employee_contrib < tax_credit_init_2) { // if e16 < h4
                  tax_credit = (employee_contrib * 0.5);
              } else {
                  tax_credit = tax_credit_init_1; // h16 = h3
              }
  
              if (age == 16) {
                  if (counter < 2) {
                      tax_credit_res = 0;
                  } else {
                      tax_credit_res = tax_credit; //tax_credit
                  }
              } else if (age == 17) {
                  if (counter < 1) {
                      tax_credit_res = 0;
                  } else {
                      tax_credit_res = tax_credit; //tax_credit
                  }
              } else {
                  tax_credit_res = tax_credit; //tax_credit
              }
  
  
              comulative_saving += employee_contrib + employer_contrib + tax_credit_res // (e16 + g16 + h16)
              return1 += employee_contrib + employer_contrib + tax_credit_res + l15
              l15 = return1 * (k3 - k1)
  
              return2 += employee_contrib + employer_contrib + tax_credit_res + n15
              n15 = return2 * (m3 - k1)
  
              return3 += employee_contrib + employer_contrib + tax_credit_res + p15
              p15 = return3 * (o3 - k1)
  
              return4 += employee_contrib + employer_contrib + tax_credit_res + r15
              r15 = return4 * (q3 - k1)
              counter++
          }
  
          console.log([minimal(return1), minimal(return2), minimal(return3), minimal(return4)]);
          return ([minimal(return1), minimal(return2), minimal(return3), minimal(return4)]);
  
      }
  
  
  
      function minimal(val) {
          if (val >= 1000) {
              return val;
          } else if (val > 500 && val < 1000) {
              return 1000;
          } else if (val > 100 && val < 500) {
              return val;
          } else if (val < 100 && val > 1) {
              return val;
          } else if (val <= 1) {
              return 0;
          }
      }
    });
  </script>
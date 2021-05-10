<script>
    console.log(<?php echo $bdm_leads_generated_quarters; ?>);
    //Lead Generation
    //Generated Lead Quarterly
    new Chart(document.getElementById("lead-generation-quarterly-generated"), {
        type: 'line',
        data: {
            labels: ["First Quarter","Second Quarter","Third Quarter","Fourth Quarter"],
            datasets: [{ 
                data: <?php echo $bdm_leads_generated_quarters; ?>,
                label: "Face-to-Face Marketers",
                borderColor: "#3e95cd",
                fill: false
            }, { 
                data: <?php echo $tm_leads_generated_quarters; ?>,
                label: "Telemarketers",
                borderColor: "#8e5ea2",
                fill: false
            }, { 
                data: <?php echo $sg_leads_generated_quarters; ?>,
                label: "Self-Generated",
                borderColor: "#3cba9f",
                fill: false
            }
            ]
        },
        options: {
            title: {
            display: true,
            text: 'Leads Generated Quarterly'
            }
        }
    });

    //Generated API from Leads Quarterly
    new Chart(document.getElementById("lead-generation-quarterly-api"), {
        type: 'line',
        data: {
            labels: ["First Quarter","Second Quarter","Third Quarter","Fourth Quarter"],
            datasets: [{ 
                data: <?php echo $bdm_api_quarters; ?>,
                label: "Face-to-Face Marketers",
                borderColor: "#3e95cd",
                fill: false
            }, { 
                data: <?php echo $tm_api_quarters; ?>,
                label: "Telemarketers",
                borderColor: "#8e5ea2",
                fill: false
            }, { 
                data: <?php echo $sg_api_quarters; ?>,
                label: "Self-Generated",
                borderColor: "#3cba9f",
                fill: false
            }
            ]
        },
        options: {
            title: {
            display: true,
            text: 'Lead Generators Quarterly Issued API'
            }
        }
    });
    //Lead Generation
    //Generated Lead Quarterly
    new Chart(document.getElementById("lead-generation-quarterly-kiwisaver"), {
        type: 'line',
        data: {
            labels: ["First Quarter","Second Quarter","Third Quarter","Fourth Quarter"],
            datasets: [{ 
                data: <?php echo $bdm_kiwisaver_deals_quarters; ?>,
                label: "Face-to-Face Marketers",
                borderColor: "#3e95cd",
                fill: false
            }, { 
                data: <?php echo $tm_kiwisaver_deals_quarters; ?>,
                label: "Telemarketers",
                borderColor: "#8e5ea2",
                fill: false
            }, { 
                data: <?php echo $sg_kiwisaver_deals_quarters; ?>,
                label: "Self-Generated",
                borderColor: "#3cba9f",
                fill: false
            }
            ]
        },
        options: {
            title: {
            display: true,
            text: 'Lead Generators KiwiSaver Deals Quarterly'
            }
        }
    });

    //Generated API from Leads Quarterly
    new Chart(document.getElementById("lead-generation-quarterly-kiwisaver-api"), {
        type: 'line',
        data: {
            labels: ["First Quarter","Second Quarter","Third Quarter","Fourth Quarter"],
            datasets: [{ 
                data: <?php echo $bdm_commission_quarters; ?>,
                label: "Face-to-Face Marketers",
                borderColor: "#3e95cd",
                fill: false
            }, { 
                data: <?php echo $tm_commission_quarters; ?>,
                label: "Telemarketers",
                borderColor: "#8e5ea2",
                fill: false
            }, { 
                data: <?php echo $sg_commission_quarters; ?>,
                label: "Self-Generated",
                borderColor: "#3cba9f",
                fill: false
            }
            ]
        },
        options: {
            title: {
            display: true,
            text: 'Lead Generators KiwiSaver API Quarterly'
            }
        }
    });

    //Production
    //Quarterly Production
    new Chart(document.getElementById("quarterly-production"), {
        type: 'line',
        data: {
            labels: ["First Quarter","Second Quarter","Third Quarter","Fourth Quarter"],
            datasets: [{ 
                data: <?php echo $submissions_quarters; ?>,
                label: "Submissions",
                borderColor: "#3e95cd",
                fill: false
            }, { 
                data: <?php echo $issued_quarters; ?>,
                label: "Policies Issued",
                borderColor: "#3cba9f",
                fill: false
            }, { 
                data: <?php echo $cancellations_quarters; ?>,
                label: "Cancellations",
                borderColor: "#ff5555",
                fill: false
            }, { 
                data: <?php echo $kiwisavers_quarters; ?>,
                label: "KiwiSavers",
                borderColor: "#ffcc00",
                fill: false
            }
            ]
        },
        options: {
            title: {
            display: true,
            text: 'Quarterly Production'
            }
        }
    });

    //Quarterly Production API
    new Chart(document.getElementById("quarterly-production-api"), {
        type: 'line',
        data: {
            labels: ["First Quarter","Second Quarter","Third Quarter","Fourth Quarter"],
            datasets: [{ 
                data: <?php echo $submissions_api_quarters; ?>,
                label: "Submissions",
                borderColor: "#3e95cd",
                fill: false
            }, { 
                data: <?php echo $issued_api_quarters; ?>,
                label: "Policies Issued",
                borderColor: "#3cba9f",
                fill: false
            }, { 
                data: <?php echo $cancellations_api_quarters; ?>,
                label: "Cancellations",
                borderColor: "#ff5555",
                fill: false
            }, { 
                data: <?php echo $kiwisavers_api_quarters; ?>,
                label: "KiwiSavers",
                borderColor: "#ffcc00",
                fill: false
            }
            ]
        },
        options: {
            title: {
            display: true,
            text: 'Quarterly Production API'
            }
        }
    });

    //Monthly Production
    new Chart(document.getElementById("monthly-production"), {
        type: 'line',
        data: {
            labels: ["January","February","March","April","May","June","July","August","September","October","November","December"],
            datasets: [{ 
                data: <?php echo $submissions_months; ?>,
                label: "Submissions",
                borderColor: "#3e95cd",
                fill: false
            }, { 
                data: <?php echo $issued_months; ?>,
                label: "Policies Issued",
                borderColor: "#3cba9f",
                fill: false
            }, { 
                data: <?php echo $cancellations_months; ?>,
                label: "Cancellations",
                borderColor: "#ff5555",
                fill: false
            }, { 
                data: <?php echo $kiwisavers_months; ?>,
                label: "KiwiSavers",
                borderColor: "#ffcc00",
                fill: false
            }
            ]
        },
        options: {
            title: {
            display: true,
            text: 'Monthly Production'
            }
        }
    });

    //Monthly Production API
    new Chart(document.getElementById("monthly-production-api"), {
        type: 'line',
        data: {
            labels: ["January","February","March","April","May","June","July","August","September","October","November","December","Total"],
            datasets: [{ 
                data: <?php echo $submissions_api_months; ?>,
                label: "Submissions",
                borderColor: "#3e95cd",
                fill: false
            }, { 
                data: <?php echo $issued_api_months; ?>,
                label: "Policies Issued",
                borderColor: "#3cba9f",
                fill: false
            }, { 
                data: <?php echo $cancellations_api_months; ?>,
                label: "Cancellations",
                borderColor: "#ff5555",
                fill: false
            }, { 
                data: <?php echo $kiwisavers_api_months; ?>,
                label: "KiwiSavers",
                borderColor: "#ffcc00",
                fill: false
            }
            ]
        },
        options: {
            title: {
            display: true,
            text: 'Monthly Production API'
            }
        }
    });
</script>
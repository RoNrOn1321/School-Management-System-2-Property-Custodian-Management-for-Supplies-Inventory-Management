<!DOCTYPE html>
<html>
<head>
    <title>Test Real API</title>
</head>
<body>
    <h2>Testing Real Asset API</h2>
    <button onclick="testCreateAsset()">Test Create Asset</button>
    <button onclick="testUpdateAsset()">Test Update Asset</button>
    <div id="results"></div>

    <script>
        async function testCreateAsset() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<p>Testing asset creation...</p>';

            const testData = {
                asset_code: 'TEST-' + Date.now(),
                name: 'Test Asset via API',
                description: 'Testing asset creation',
                category: 'Electronics',
                status: 'available',
                condition_status: 'good',
                location: 'Test Location',
                purchase_cost: '200.00',
                current_value: '180.00'
            };

            try {
                console.log('Sending data:', testData);

                const response = await fetch('./api/assets.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(testData)
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', [...response.headers.entries()]);

                const responseText = await response.text();
                console.log('Raw response:', responseText);

                let result;
                try {
                    result = JSON.parse(responseText);
                    console.log('Parsed response:', result);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    result = { error: 'Invalid JSON response', rawResponse: responseText };
                }

                resultsDiv.innerHTML = `
                    <h3>Create Asset Test Results:</h3>
                    <p><strong>Status:</strong> ${response.status}</p>
                    <p><strong>Success:</strong> ${response.ok ? 'Yes' : 'No'}</p>
                    <p><strong>Response:</strong></p>
                    <pre>${JSON.stringify(result, null, 2)}</pre>
                    <p><strong>Raw Response:</strong></p>
                    <pre>${responseText}</pre>
                `;

            } catch (error) {
                console.error('Request failed:', error);
                resultsDiv.innerHTML = `
                    <h3>Create Asset Test - ERROR:</h3>
                    <p style="color: red;">${error.message}</p>
                `;
            }
        }

        async function testUpdateAsset() {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '<p>Testing asset update...</p>';

            const testData = {
                asset_id: '1', // Assuming asset ID 1 exists
                name: 'Updated Test Asset',
                description: 'Testing asset update',
                category: 'Electronics',
                status: 'available',
                condition_status: 'good',
                location: 'Updated Location'
            };

            try {
                console.log('Sending update data:', testData);

                const response = await fetch('./api/assets.php?id=1', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(testData)
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', [...response.headers.entries()]);

                const responseText = await response.text();
                console.log('Raw response:', responseText);

                let result;
                try {
                    result = JSON.parse(responseText);
                    console.log('Parsed response:', result);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    result = { error: 'Invalid JSON response', rawResponse: responseText };
                }

                resultsDiv.innerHTML = `
                    <h3>Update Asset Test Results:</h3>
                    <p><strong>Status:</strong> ${response.status}</p>
                    <p><strong>Success:</strong> ${response.ok ? 'Yes' : 'No'}</p>
                    <p><strong>Response:</strong></p>
                    <pre>${JSON.stringify(result, null, 2)}</pre>
                    <p><strong>Raw Response:</strong></p>
                    <pre>${responseText}</pre>
                `;

            } catch (error) {
                console.error('Request failed:', error);
                resultsDiv.innerHTML = `
                    <h3>Update Asset Test - ERROR:</h3>
                    <p style="color: red;">${error.message}</p>
                `;
            }
        }
    </script>
</body>
</html>
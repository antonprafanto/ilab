// Quick test script untuk validasi setup ILab UNMUL
const http = require('http');

console.log('🔍 Testing ILab UNMUL Setup...\n');

// Test Frontend
function testFrontend() {
  return new Promise((resolve) => {
    const req = http.get('http://localhost:3005', (res) => {
      if (res.statusCode === 200) {
        console.log('✅ Frontend: Running on http://localhost:3005');
        resolve(true);
      } else {
        console.log('❌ Frontend: Not responding properly');
        resolve(false);
      }
    });
    
    req.on('error', () => {
      console.log('❌ Frontend: Not running on port 3005');
      console.log('   → Run: cd client && npm run dev -- --port 3005');
      resolve(false);
    });
    
    req.setTimeout(5000, () => {
      console.log('❌ Frontend: Timeout');
      resolve(false);
    });
  });
}

// Test Backend
function testBackend() {
  return new Promise((resolve) => {
    const req = http.get('http://localhost:3003/api/v1/health', (res) => {
      if (res.statusCode === 200) {
        console.log('✅ Backend: Running on http://localhost:3003');
        resolve(true);
      } else {
        console.log('❌ Backend: API not responding properly');
        resolve(false);
      }
    });
    
    req.on('error', () => {
      console.log('❌ Backend: Not running on port 3002');
      console.log('   → Run: cd server && npm run dev');
      resolve(false);
    });
    
    req.setTimeout(5000, () => {
      console.log('❌ Backend: Timeout');
      resolve(false);
    });
  });
}

// Test MySQL connection (via backend health check)
function testDatabase() {
  return new Promise((resolve) => {
    const req = http.get('http://localhost:3003/api/v1/health', (res) => {
      let data = '';
      res.on('data', chunk => data += chunk);
      res.on('end', () => {
        try {
          const result = JSON.parse(data);
          if (result.database === 'connected') {
            console.log('✅ Database: MySQL connected');
            resolve(true);
          } else {
            console.log('❌ Database: Connection failed');
            console.log('   → Check MySQL setup in SETUP-MYSQL.md');
            resolve(false);
          }
        } catch {
          console.log('❌ Database: Health check failed');
          resolve(false);
        }
      });
    });
    
    req.on('error', () => {
      console.log('❌ Database: Cannot test (backend not running)');
      resolve(false);
    });
    
    req.setTimeout(5000, () => {
      console.log('❌ Database: Health check timeout');
      resolve(false);
    });
  });
}

// Run tests
async function runTests() {
  const frontendOk = await testFrontend();
  const backendOk = await testBackend();
  
  if (backendOk) {
    await testDatabase();
  }
  
  console.log('\n📋 Summary:');
  if (frontendOk && backendOk) {
    console.log('🎉 ILab UNMUL is ready for testing!');
    console.log('   → Frontend: http://localhost:3005');
    console.log('   → Backend API: http://localhost:3003/api/v1');
  } else {
    console.log('⚠️  Some services are not running.');
    console.log('   📖 Check start-local.md for setup instructions');
  }
}

runTests();
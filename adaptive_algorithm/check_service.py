"""
Quick script to check if the adaptive assessment service is running
"""

import requests
import sys

SERVICE_URL = 'http://localhost:5000'

def check_service():
    """Check if the service is running and responding"""
    try:
        print("Checking adaptive assessment service...")
        print(f"Service URL: {SERVICE_URL}")
        print("-" * 50)
        
        # Test health endpoint
        response = requests.get(f"{SERVICE_URL}/health", timeout=5)
        
        if response.status_code == 200:
            data = response.json()
            print("✅ Service is RUNNING!")
            print(f"   Status: {data.get('status', 'unknown')}")
            print(f"   Service: {data.get('service', 'unknown')}")
            return True
        else:
            print(f"❌ Service responded with status code: {response.status_code}")
            return False
            
    except requests.exceptions.ConnectionError:
        print("❌ Service is NOT running!")
        print("   Connection refused - the service is not accessible on port 5000")
        print("\n   To start the service, run:")
        print("   python adaptive_service.py")
        return False
        
    except requests.exceptions.Timeout:
        print("❌ Service is NOT responding!")
        print("   Request timed out")
        return False
        
    except Exception as e:
        print(f"❌ Error checking service: {e}")
        return False

if __name__ == '__main__':
    is_running = check_service()
    sys.exit(0 if is_running else 1)



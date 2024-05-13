import { useEffect, useState } from 'react'
import { Link, useLocation } from 'react-router-dom'

// import fontawesome
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faImage, faVideo, faFolder } from '@fortawesome/free-solid-svg-icons';

// engal components
import LoadingComponent from '../LoadingComponent'
import ErrorMessageComponent from '../error/ErrorMessageComponent'

/**
 * User panel navigation
 */
export default function UserNavigationComponent() {
    // storage data
    let apiUrl = localStorage.getItem('api-url')
    let loginToken = localStorage.getItem('login-token')

    // get user app location
    const location = useLocation()

    // status state
    const [userData, setUserData] = useState([])
    const [loading, setLoading] = useState(true)
    const [stats, setStats] = useState(null)
    
    // fetch dashboard/user data
    useEffect(() => {
        const fetchUserData = async () => {
            // check if user loggedin
            if (loginToken != null) {
                try {
                    // build request
                    const response = await fetch(apiUrl + '/api/user/status', {
                        method: 'GET',
                        headers: {
                            'Accept': '*/*',
                            'Authorization': 'Bearer ' + localStorage.getItem('login-token')
                        },
                    })

                    // get response data
                    const data = await response.json()
                        
                    // check if user tokne is valid
                    if (data.status == 'success') {
                        setUserData({
                            username: data.user_status.username,
                            roles: data.user_status.roles,
                        })
                        setStats(data.stats)
                    } else {
                        return <ErrorMessageComponent message={data.message}/>                   
                    }
                } catch (error) {
                    if (DEV_MODE) {
                        console.log('error: ' + error)
                    }
                } finally {
                    setLoading(false)
                }
            }
        }
        fetchUserData()
    }, [apiUrl, loginToken])

    // show loading
    if (loading) {
        return <LoadingComponent/>
    }

    // get role color
    const textColor = userData.roles.includes('ROLE_ADMIN') ? 'red' : 'green'

    return (
        <div>
            <div className="user-navbar">
                {/* main home link */}
                <span className="breadcrumb">
                    <span>
                        <span className="slash">/</span>
                        <Link to="/" className="sub-navigation-link">home</Link>
                    </span>

                    {/* upload navigation */}
                    {location.pathname == "/upload" ? 
                        <span>
                            <span className="slash">/</span>
                            <Link to="/upload" className="sub-navigation-link">upload</Link> 
                        </span>
                    : null}
                </span>

                <div className="user-data">
                    <div className="username">
                        <p className={`color-${textColor}`}>{userData.username}</p>
                    </div>
                    <span className="spacer">|</span>
                    <div className="count-bar">
                        <snap className="counter-element"><FontAwesomeIcon icon={faImage}/> {stats.images_count}</snap>
                        <snap className="counter-element"><FontAwesomeIcon icon={faVideo}/> {stats.videos_count}</snap>
                        <snap className="counter-element"><FontAwesomeIcon icon={faFolder}/> {stats.galleries_count}</snap>
                    </div>
                </div>
            </div>
        </div>
    )
}

import React, { useEffect, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import axios from "axios";

const Navbar = () => {
  const [username, setUsername] = useState("");
  const navigate = useNavigate();

  // Fetch logged-in user info
  useEffect(() => {
    const fetchUser = async () => {
      try {
        const token = localStorage.getItem("token");
        if (!token) {
          navigate("/login");
          return;
        }

        const response = await axios.get(
          `${process.env.REACT_APP_API_BASE_URL}/checkAuth.php`,
          {
            headers: {
              Authorization: `Bearer ${token}`,
            },
          }
        );

        if (response.data && response.data.username) {
          setUsername(response.data.username);
        } else {
          navigate("/login");
        }
      } catch (error) {
        console.error("Auth check failed", error);
        navigate("/login");
      }
    };

    fetchUser();
  }, [navigate]);

  // Logout handler
  const handleLogout = async () => {
    try {
      await axios.post(`${process.env.REACT_APP_API_BASE_URL}/logout.php`);
    } catch (error) {
      console.error("Logout failed", error);
    } finally {
      localStorage.removeItem("token");
      navigate("/login");
    }
  };

  return (
    <nav className="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
      <div className="container">
        <Link className="navbar-brand" to="/">
          BlogSite
        </Link>
        <div className="collapse navbar-collapse">
          <ul className="navbar-nav me-auto">
            <li className="nav-item">
              <Link className="nav-link" to="/">
                Posts
              </Link>
            </li>
            <li className="nav-item">
              <Link className="nav-link" to="/create">
                Create Post
              </Link>
            </li>
          </ul>
          <span className="navbar-text me-3">
            Logged in as <strong>{username}</strong>
          </span>
          <button className="btn btn-outline-light" onClick={handleLogout}>
            Logout
          </button>
        </div>
      </div>
    </nav>
  );
};

export default Navbar;

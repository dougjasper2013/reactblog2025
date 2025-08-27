import React, { useState, useEffect } from "react";
import { useParams } from "react-router-dom";
import axios from "axios";

const Post = () => {
  const { id } = useParams();
  const [post, setPost] = useState(null);

  const fetchPost = async () => {
    try {
      const response = await axios.get(`${process.env.REACT_APP_API_BASE_URL}/post.php/${id}`);
      setPost(response.data.data);
    } catch (error) {
      console.log(error);
    }
  };

  const handleVote = async (type) => {
    try {
      await axios.post(`${process.env.REACT_APP_API_BASE_URL}/vote.php`, {
        post_id: id,
        vote_type: type
      });
      // Refresh post to update like/dislike counts
      fetchPost();
    } catch (error) {
      console.error("Error submitting vote:", error);
    }
  };

  useEffect(() => {
    fetchPost();
  }, []);

  if (!post) {
    return <div>Loading...</div>;
  }

  return (
    <div className="container my-4">
      <h1 className="mb-4">{post.title}</h1>
      <p>{post.content}</p>
      <hr />
      <div className="d-flex justify-content-center mb-3">
        <small className="text-muted">
          Posted by {post.author} on {post.date}
        </small>
      </div>

      <div className="d-flex justify-content-center gap-3">
        <button className="btn btn-success" onClick={() => handleVote("like")}>
          👍 Like ({post.likes})
        </button>
        <button className="btn btn-danger" onClick={() => handleVote("dislike")}>
          👎 Dislike ({post.dislikes})
        </button>
      </div>
    </div>
  );
};

export default Post;

import React, { useState } from "react";
import { useParams } from "react-router-dom";
import axios from "axios";

const Post = () => {

    const { id } = useParams();
    const [post, setPost] = useState(null);

    const fetchPost = async () => {
        try
        {
            const response = await axios.get(`${process.env.REACT_APP_API_BASE_URL}/post.php/${id}`);
            const post = response.data.data;
            setPost(post);
        }
        catch (error)
        {
            console.log(error);
        }
    };

    React.useEffect(() => {
        fetchPost();
    }, []);

    if (!post)
    {
        return <div>Loading...</div>;
    }

    return (
        <div className="container my-4">
            <h1 className="mb-4">{post.title}</h1>
            <p>{post.content}</p>
            <hr />
            <div className="d-flex justify-content-center">
                <div>
                    <small className="text-muted">
                        Posted by {post.author} on {post.date}
                    </small>
                </div>
            </div>
        </div>
    );

};

export default Post;